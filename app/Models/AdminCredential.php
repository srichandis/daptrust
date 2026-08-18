<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class AdminCredential extends Model
{
    protected $fillable = [
        'username',
        'password',
        'security_question',
        'security_answer',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'security_answer',
        'remember_token',
    ];

    /**
     * Return the single admin credential row, seeding it from the ADMIN_*
     * env configuration the first time the app is used.
     *
     * The row is found by "first" rather than a hard-coded id so that
     * sqlite-backed tests (whose autoincrement sequence does not roll back
     * with transactions) keep resolving to the seeded row.
     */
    public static function ensureSeeded(): self
    {
        return static::firstOrCreate([], [
            'username' => (string) config('admin.username'),
            'password' => Hash::make((string) config('admin.password')),
            'security_question' => (string) config('admin.security_question'),
            'security_answer' => Hash::make((string) config('admin.security_answer')),
        ]);
    }
}
