<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Modules\Auth\DTOs\RegisterDTO;
use App\Modules\User\Models\User;

class AuthService {

    /**
     * Yeni kullanıcı kaydı ve token oluşturma işlemi.
     * Array dönüş tipi {user: User, token: string} şeklindedir.
     */

    public function register (RegisterDTO $dto): array {
        // Kayıt işlemi burada yapılacak
        return DB::transaction(function )
    }

}
