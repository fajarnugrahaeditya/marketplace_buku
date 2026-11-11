<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->string('type', 10); // full atau preview
            $table->string('path'); // disk privat, bukan folder public
            $table->unsignedInteger('size')->nullable();
            $table->string('format', 10)->nullable()->default('pdf');
            $table->timestamps();

            $table->foreign('book_id')->references('id')->on('books')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });

        DB::statement("ALTER TABLE book_files ADD CONSTRAINT chk_book_files_type CHECK (type IN ('full', 'preview'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('book_files');
    }
};
