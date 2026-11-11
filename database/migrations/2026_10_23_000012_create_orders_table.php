<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('order_number', 30)->unique();
            $table->unsignedInteger('total');
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('expires_at')->nullable(); // batas bayar (default 24 jam)
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')
                ->restrictOnDelete()->cascadeOnUpdate();
        });

        DB::statement("ALTER TABLE orders ADD CONSTRAINT chk_orders_status CHECK (status IN ('pending', 'paid', 'expired', 'cancelled'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
