<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'nama'          => fake()->name(),
            'email'         => fake()->unique()->safeEmail(),
            'password'      => static::$password ??= Hash::make('password'),
            'nomor_telepon' => fake()->phoneNumber(),
            'role'          => 'penyewa',
            'status_akun'   => 'aktif',
            'remember_token' => Str::random(10),
        ];
    }
}
