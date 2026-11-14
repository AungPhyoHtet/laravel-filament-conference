<?php

namespace App\Filament\Speaker\Pages\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Auth\Pages\Register as BaseRegister;
use Illuminate\Database\Eloquent\Model;
use SensitiveParameter;

class Register extends BaseRegister
{
    /**
     * Sign-ups are always speakers, and each one gets a fresh speaker profile to submit talks under.
     *
     * @param  array<string, mixed>  $data
     */
    protected function handleRegistration(#[SensitiveParameter] array $data): Model
    {
        /** @var User $user */
        $user = $this->getUserModel()::create([
            ...$data,
            'role' => UserRole::Speaker,
        ]);

        $user->speaker()->create([
            'name' => $user->name,
            'email' => $user->email,
            'bio' => '',
            'twitter_handle' => '',
        ]);

        return $user;
    }
}
