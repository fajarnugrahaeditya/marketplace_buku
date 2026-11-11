<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('book_id');
            $table->unsignedInteger('price'); // harga saat transaksi
            $table->unsignedTinyInteger('royalty_percent'); // persentase penulis saat transaksi
            $table->unsignedInteger('author_share');
            $table->unsignedInteger('platform_share');
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('book_id')->references('id')->on('books')
                ->restrictOnDelete()->cascadeOnUpdate();
        });

        DB::statement('ALTER TABLE order_items ADD CONSTRAINT chk_order_items_percent CHECK (royalty_percent BETWEEN 0 AND 100)');
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
