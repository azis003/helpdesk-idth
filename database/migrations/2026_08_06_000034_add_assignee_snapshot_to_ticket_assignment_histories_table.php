<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_assignment_histories', function (Blueprint $table): void {
            $table->string('to_user_name_snapshot', 150)->nullable()->after('to_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_assignment_histories', function (Blueprint $table): void {
            $table->dropColumn('to_user_name_snapshot');
        });
    }
};
