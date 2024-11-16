<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->double('far')->nullable()->default(0)->after('price');
            $table->double('total_price')->virtualAs('price + far')->comment('Auto calc')->nullable()->after('far');
            $table->dropConstrainedForeignId('category_id');
            $table->foreignId('weight_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('size_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('type_id')->nullable()->constrained('categories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['far','total_price']);
            $table->dropConstrainedForeignId('weight_id');
            $table->dropConstrainedForeignId('size_id');
            $table->dropConstrainedForeignId('type_id');

        });
    }
};
