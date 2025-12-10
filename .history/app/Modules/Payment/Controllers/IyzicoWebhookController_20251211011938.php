<?php

declare(strict_types=1);

namespace App\Modules\Payment\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Models\PaymentTransaction;
use App\Modules\Payment\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class IyzicoWebhookController extends Controller
{
    public function handlePayment(Request $request): JsonResponse
    {
        // 1. Logla (İstek geldi)
        Log::info('Iyzico webhook received', ['ip' => $request->ip()]);

        // 2. İmza Doğrulama (Signature Verification)
        // Dokümandaki gibi: X-Iyzico-Signature header'ını kontrol etmeliyiz.
        // Localhost'ta test ederken bu header olmayacağı için 'local' ortamda bypass edebiliriz.
        if (app()->environment() !== 'local' && !$this->verifySignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $payload = $request->all();

        // 3. İlgili İşlemi Bul (Transaction Lookup)
        // İyzico'dan 'paymentId' veya bizim gönderdiğimiz 'conversationId' (idempotency_key) gelir.
        $transactionId = $payload['paymentId'] ?? null;
        $conversationId = $payload['conversationId'] ?? null;

        $transaction = PaymentTransaction::where('transaction_id', $transactionId)
            ->orWhere('idempotency_key', $conversationId)
            ->first();

        if (!$transaction) {
            // Transaction bulunamadıysa bile 200 dönüyoruz ki İyzico sürekli retry yapmasın.
            // Ama loga "Warning" basıyoruz.
            Log::warning('Webhook transaction not found', $payload);
            return response()->json(['status' => 'ok']);
        }

        // 4. Idempotency Kontrolü (Daha önce işlendi mi?)
        if ($this->isAlreadyProcessed($transaction, $payload)) {
            Log::info('Webhook already processed', ['transaction_id' => $transaction->id]);
            return response()->json(['status' => 'ok']);
        }

        // 5. İşle (Success/Fail)
        // Burada Transaction status güncellenecek ve Job fırlatılacak.
        // Şimdilik logluyoruz.
        $this->processWebhook($transaction, $payload);

        return response()->json(['status' => 'ok']);
    }

    private function verifySignature(Request $request): bool
    {
        $signature = $request->header('X-Iyzico-Signature');
        if (!$signature) return false;

        $payload = $request->getContent();
        $secret = config('services.iyzico.secret_key'); // .env'den almalı

        // HMAC-SHA256 ile imzala
        $expectedSignature = base64_encode(hash_hmac('sha256', $payload, $secret, true));

        // Zamanlama saldırılarına karşı hash_equals kullan
        return hash_equals($expectedSignature, $signature);
    }

    private function isAlreadyProcessed(PaymentTransaction $transaction, array $payload): bool
    {
        // Eğer zaten success veya failed ise tekrar işleme
        if (in_array($transaction->status, ['success', 'failed'])) {
            return true;
        }

        // Veya WebhookLog tablosunda aynı hash varsa işleme
        $hash = md5(json_encode($payload));
        return WebhookLog::where('transaction_id', $transaction->id)
            ->where('payload_hash', $hash)
            ->where('status', 'processed')
            ->exists();
    }

    private function processWebhook(PaymentTransaction $transaction, array $payload): void
    {
        // Log kaydı oluştur
        WebhookLog::create([
            'transaction_id' => $transaction->id,
            'provider' => 'iyzico',
            'event' => 'payment.' . ($payload['status'] ?? 'unknown'),
            'payload' => json_encode($payload),
            'payload_hash' => md5(json_encode($payload)),
            'status' => 'processed',
            'source_ip' => request()->ip(),
            'processed_at' => now(),
        ]);

        // Transaction güncelle
        if (($payload['status'] ?? '') === 'success') {
            $transaction->update([
                'status' => 'success',
                'completed_at' => now(),
                'raw_response' => json_encode($payload),
            ]);

            // TODO: Burada FinalizeOrderJob veya FinalizeTransferJob tetiklenecek
        } else {
            $transaction->update([
                'status' => 'failed',
                'failed_at' => now(),
                'raw_response' => json_encode($payload),
            ]);
        }
    }
}
