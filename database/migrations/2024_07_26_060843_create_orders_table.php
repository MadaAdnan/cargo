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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('qr_url')->nullable();
            $table->string('qr_code')->nullable();
            $table->string('options')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            $table->string('type')->nullable()->comment('OrderTypeEnum');
            $table->string('status')->nullable()->default('pending')->comment('OrderStatusEnum');
            $table->foreignId('branch_source_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('branch_target_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->date('shipping_date')->nullable();
            $table->foreignId('sender_id')->nullable()->comment('Users ID')->constrained('users')->nullOnDelete();
            $table->string('sender_phone')->nullable();
            $table->string('sender_address')->nullable();
            $table->foreignId('receive_id')->nullable()->comment('Users ID')->constrained('users')->nullOnDelete();
            $table->string('receive_phone')->nullable();
            $table->string('receive_address')->nullable();
            $table->foreignId('city_source_id')->nullable()->comment('City ID')->constrained('cities')->nullOnDelete();
            $table->foreignId('city_target_id')->nullable()->comment('City ID')->constrained('cities')->nullOnDelete();
            $table->string('bay_type')->nullable()->default('after')->comment('BayTypeEnum');
            $table->double('price')->nullable();
            $table->double('total_weight')->nullable();
            $table->string('canceled_info')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
