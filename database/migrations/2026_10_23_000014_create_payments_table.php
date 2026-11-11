<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('midtrans_order_id', 60)->unique();
            $table->string('method', 30)->nullable();
            $table->string('status', 20); // status dari Midtrans
            $table->timestamp('paid_at')->nullable();
            $table->json('payload')->nullable(); // isi notifikasi webhook
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')
                ->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
