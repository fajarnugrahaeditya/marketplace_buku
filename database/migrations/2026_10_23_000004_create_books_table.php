<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('author_id');
            $table->string('title', 200);
            $table->string('slug', 220)->unique();
            $table->text('synopsis')->nullable();
            $table->string('cover')->nullable();
            $table->unsignedInteger('price')->default(0); // 0 = gratis; minimum buku berbayar ada di tabel settings
            $table->string('status', 20)->default('draft')->index();
            $table->text('rejected_reason')->nullable();
            $table->text('takedown_reason')->nullable();
            $table->string('license_type', 30)->default('own_work');
            $table->string('source_url')->nullable(); // tautan sumber atau tautan bukti izin
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('author_id')->references('user_id')->on('author_profiles')
                ->restrictOnDelete()->cascadeOnUpdate();
        });

        DB::statement("ALTER TABLE books ADD CONSTRAINT chk_books_status CHECK (status IN ('draft', 'pending', 'published', 'rejected', 'taken_down', 'archived'))");
        DB::statement("ALTER TABLE books ADD CONSTRAINT chk_books_license CHECK (license_type IN ('own_work', 'creative_commons', 'public_domain', 'with_permission'))");
        // AB-08 dan AB-10: karya bukan milik sendiri wajib punya tautan sumber atau bukti izin
        DB::statement("ALTER TABLE books ADD CONSTRAINT chk_books_source CHECK (license_type = 'own_work' OR source_url IS NOT NULL)");
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
