<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('author_id');
            $table->unsignedInteger('amount');
            $table->string('bank_account', 100);
            $table->string('status', 20)->default('requested')->index();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('author_id')->references('user_id')->on('author_profiles')
                ->restrictOnDelete()->cascadeOnUpdate();
        });

        DB::statement("ALTER TABLE withdrawals ADD CONSTRAINT chk_withdrawals_status CHECK (status IN ('requested', 'approved', 'transferred', 'rejected'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
