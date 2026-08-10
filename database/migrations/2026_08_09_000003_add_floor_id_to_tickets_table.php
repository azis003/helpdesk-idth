<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->foreignId('floor_id')
                ->nullable()
                ->after('room_id')
                ->constrained('floors')
                ->restrictOnDelete();
        });

        DB::table('tickets')
            ->whereNotNull('room_id')
            ->orderBy('id')
            ->get(['id', 'room_id'])
            ->each(function (object $ticket): void {
                $floorId = DB::table('rooms')
                    ->where('id', $ticket->room_id)
                    ->value('floor_id');

                if ($floorId !== null) {
                    DB::table('tickets')
                        ->where('id', $ticket->id)
                        ->update(['floor_id' => $floorId]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropForeign(['floor_id']);
            $table->dropColumn('floor_id');
        });
    }
};
