<?php

declare(strict_types=1);

namespace App\Modules\Auth\Events;

use App\Modules\User\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
    ) {}
}
