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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('file_number')->unique();
            $table->string('title');
            $table->text('description');
            $table->string('priority')->default('normal'); // normal, urgent, most_urgent
            $table->string('status')->default('pending'); // draft, pending, forwarded, approved, rejected, completed
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('current_holder_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('current_department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
