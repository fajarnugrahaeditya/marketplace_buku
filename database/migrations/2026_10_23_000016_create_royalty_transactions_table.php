<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('royalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('author_id');
            $table->unsignedBigInteger('order_item_id')->unique(); // satu item pesanan, satu catatan royalti
            $table->unsignedInteger('amount');
            $table->timestamps();

            $table->foreign('author_id')->references('user_id')->on('author_profiles')
                ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('order_item_id')->references('id')->on('order_items')
                ->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('royalty_transactions');
    }
};
