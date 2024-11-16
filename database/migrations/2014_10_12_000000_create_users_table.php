<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique()->nullable();
            $table->string('num_id')->unique()->nullable();
            $table->string('iban')->unique()->nullable();
            $table->string('market_name')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('url')->nullable();
            $table->string('address')->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->json('location')->nullable();
            $table->string('status')->nullable()->default('pending')->comment('ActivateStatusEnum');
            $table->string('level')->nullable()->default('user')->comment('LevelUserEnum');
            $table->string('full_name')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('job')->nullable()->comment('JobUserEnum');

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
