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
        Schema::table('comments', function (Blueprint $table) {
            $table->string('attachment_path')->nullable()->after('content');
        });

        Schema::table('document_tracks', function (Blueprint $table) {
            $table->string('attachment_path')->nullable()->after('remarks');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->text('urgency_remarks')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn('attachment_path');
        });

        Schema::table('document_tracks', function (Blueprint $table) {
            $table->dropColumn('attachment_path');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('urgency_remarks');
        });
    }
};
