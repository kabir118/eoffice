<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_queries', function (Blueprint $table) {
            $table->string('subject')->nullable()->after('user_id');
        });

        // Set existing records' subjects to a snippet of their message
        $tickets = DB::table('support_queries')->get();
        foreach ($tickets as $ticket) {
            $snippet = substr(strip_tags($ticket->message), 0, 80);
            if (empty($snippet)) {
                $snippet = 'Support Query #' . $ticket->id;
            }
            DB::table('support_queries')->where('id', $ticket->id)->update([
                'subject' => $snippet
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('support_queries', function (Blueprint $table) {
            $table->dropColumn('subject');
        });
    }
};
