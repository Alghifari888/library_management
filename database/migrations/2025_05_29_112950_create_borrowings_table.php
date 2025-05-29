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
        Schema::create('borrowings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('Member who borrowed')->constrained('users')->onDelete('cascade');
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            
            // Mengubah dari timestamp ke dateTime untuk menghindari masalah default value di MySQL
            $table->dateTime('borrowed_at'); 
            $table->dateTime('due_at');
            
            $table->timestamp('returned_at')->nullable();
            $table->string('status')->default('borrowed'); // borrowed, returned, overdue
            $table->foreignId('processed_by_user_id')->nullable()->comment('Officer/Admin who processed')->constrained('users')->onDelete('set null');
            $table->timestamps(); // Ini akan membuat created_at dan updated_at sebagai timestamp
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrowings');
    }
};