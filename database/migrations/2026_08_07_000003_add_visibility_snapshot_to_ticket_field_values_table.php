<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_field_values', function (Blueprint $table): void {
            $table->string('visibility_snapshot', 20)
                ->default('requester')
                ->after('field_type_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_field_values', function (Blueprint $table): void {
            $table->dropColumn('visibility_snapshot');
        });
    }
};
