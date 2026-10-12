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
        Schema::create('cash_flows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['in', 'out']); // in = uang masuk, out = uang keluar
            $table->string('category', 100); // Penjualan, Pembayaran Hutang, Kulakan, Pengeluaran, Kas Awal, dll.
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('reference_type', 50)->nullable(); // sale, purchase, expense, debt_payment, manual
            $table->unsignedBigInteger('reference_id')->nullable()->index();
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_flows');
    }
};
