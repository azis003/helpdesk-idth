<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attachments', function (Blueprint $table): void {
            $table->foreignId('ticket_comment_id')
                ->nullable()
                ->after('ticket_id')
                ->constrained('ticket_comments')
                ->nullOnDelete();
            $table->index(['ticket_id', 'ticket_comment_id']);
        });
    }

    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table): void {
            $table->dropForeign(['ticket_comment_id']);
            $table->dropIndex('attachments_ticket_id_ticket_comment_id_index');
            $table->dropColumn('ticket_comment_id');
        });
    }
};
