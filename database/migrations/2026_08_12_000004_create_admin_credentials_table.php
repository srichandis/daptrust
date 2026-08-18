<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Single-row store for the admin credential. Seeded from the ADMIN_*
     * env vars on first use so the password can later be changed in-app
     * (forgot-password flow) and "remember me" tokens can persist.
     */
    public function up(): void
    {
        Schema::create('admin_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('password');
            $table->string('security_question')->nullable();
            $table->string('security_answer')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_credentials');
    }
};
