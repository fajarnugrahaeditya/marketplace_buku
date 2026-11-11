<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id'); // pembaca yang mengikuti
            $table->unsignedBigInteger('author_id'); // penulis yang diikuti

            $table->primary(['user_id', 'author_id']);

            $table->foreign('user_id')->references('id')->on('users')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('author_id')->references('user_id')->on('author_profiles')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });

        // AB-14: tidak boleh mengikuti diri sendiri.
        // CHECK tidak bisa dipakai di MariaDB karena kolom ini bagian dari foreign key,
        // jadi dijaga dengan trigger satu pernyataan.
        DB::unprepared('CREATE TRIGGER trg_follows_no_self BEFORE INSERT ON follows FOR EACH ROW SET NEW.user_id = IF(NEW.user_id = NEW.author_id, NULL, NEW.user_id)');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_follows_no_self');
        Schema::dropIfExists('follows');
    }
};
