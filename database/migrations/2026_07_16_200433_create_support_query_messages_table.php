<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_query_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_query_id')->constrained('support_queries')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('message');
            $table->timestamps();
        });

        // Migrate existing ticket data to messages
        $tickets = DB::table('support_queries')->get();
        foreach ($tickets as $ticket) {
            // First message is the original ticket query message
            DB::table('support_query_messages')->insert([
                'support_query_id' => $ticket->id,
                'user_id' => $ticket->user_id,
                'message' => $ticket->message,
                'created_at' => $ticket->created_at,
                'updated_at' => $ticket->created_at,
            ]);

            // Second message is the admin notes (if they exist)
            if (!empty($ticket->admin_notes)) {
                // Find first super admin
                $admin = DB::table('users')->where('role', 'super_admin')->first();
                $adminId = $admin ? $admin->id : $ticket->user_id;

                DB::table('support_query_messages')->insert([
                    'support_query_id' => $ticket->id,
                    'user_id' => $adminId,
                    'message' => $ticket->admin_notes,
                    'created_at' => $ticket->updated_at,
                    'updated_at' => $ticket->updated_at,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('support_query_messages');
    }
};
