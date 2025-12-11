<!DOCTYPE html>
<html>

<head>
    <title>Sipariş Onayı</title>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
        <h2 style="color: #4F46E5;">Teşekkürler {{ $order->user->name }}! 🎉</h2>

        <p>Siparişiniz başarıyla alındı. İyi eğlenceler dileriz!</p>

        <div style="background-color: #f9fafb; padding: 15px; margin: 20px 0;">
            <h3>Etkinlik Detayları</h3>
            <p><strong>Etkinlik:</strong> {{ $order->event->name }}</p>
            <p><strong>Mekan:</strong> {{ $order->event->venue->name }}</p>
            <p><strong>Tarih:</strong> {{ $order->event->start_time->format('d.m.Y H:i') }}</p>
            <p><strong>Adet:</strong> {{ $order->quantity }}</p>
            <p><strong>Toplam Tutar:</strong> {{ $order->total_amount }}₺</p>
        </div>

        <h3>Bilet Kodlarınız:</h3>
        <ul>
            @foreach ($order->tickets as $ticket)
                <li style="font-size: 18px; font-weight: bold; color: #DC2626;">
                    {{ $ticket->code }}
                </li>
            @endforeach
        </ul>

        <hr>
        <small style="color: #666;">Bu e-posta Eventgram tarafından otomatik gönderilmiştir.</small>
    </div>
</body>

</html>
