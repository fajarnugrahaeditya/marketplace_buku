<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('author_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->string('pen_name', 100);
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedInteger('balance')->default(0); // saldo royalti (rupiah)
            $table->timestamp('rights_agreed_at')->nullable(); // persetujuan pernyataan hak distribusi
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('author_profiles');
    }
};
