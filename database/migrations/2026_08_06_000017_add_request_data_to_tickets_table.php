<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('ticket_number', 20)->nullable()->unique()->after('id');
            $table->string('ticket_class', 3)->nullable()->index()->after('ticket_number');
            $table->unsignedSmallInteger('ticket_year')->nullable()->after('ticket_class');
            $table->unsignedInteger('ticket_sequence')->nullable()->after('ticket_year');
            $table->foreignId('requester_id')->nullable()->after('subject')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by_id')->nullable()->after('requester_id')->constrained('users')->restrictOnDelete();
            $table->string('requester_name_snapshot', 150)->nullable();
            $table->string('requester_nip_snapshot', 32)->nullable();
            $table->string('requester_team_snapshot', 150)->nullable();
            $table->boolean('is_self_created')->default(true)->index();
            $table->foreignId('service_type_id')->nullable()->constrained('service_types')->restrictOnDelete();
            $table->foreignId('service_type_variant_id')->nullable()->constrained('service_type_variants')->restrictOnDelete();
            $table->string('service_type_code_snapshot', 20)->nullable();
            $table->string('service_type_name_snapshot', 150)->nullable();
            $table->string('service_type_variant_code_snapshot', 60)->nullable();
            $table->string('service_type_variant_label_snapshot', 150)->nullable();
            $table->string('priority', 20)->nullable()->index();
            $table->text('description')->nullable();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->restrictOnDelete();
            $table->string('building_name_snapshot', 150)->nullable();
            $table->string('floor_name_snapshot', 100)->nullable();
            $table->string('room_name_snapshot', 100)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unique(['ticket_class', 'ticket_year', 'ticket_sequence']);
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropUnique('tickets_ticket_number_unique');
            $table->dropUnique('tickets_ticket_class_ticket_year_ticket_sequence_unique');
            $table->dropIndex('tickets_ticket_class_index');
            $table->dropIndex('tickets_is_self_created_index');
            $table->dropIndex('tickets_priority_index');
            $table->dropForeign(['requester_id']);
            $table->dropForeign(['created_by_id']);
            $table->dropForeign(['service_type_id']);
            $table->dropForeign(['service_type_variant_id']);
            $table->dropForeign(['room_id']);
            $table->dropColumn([
                'ticket_number',
                'ticket_class',
                'ticket_year',
                'ticket_sequence',
                'requester_id',
                'created_by_id',
                'requester_name_snapshot',
                'requester_nip_snapshot',
                'requester_team_snapshot',
                'is_self_created',
                'service_type_id',
                'service_type_variant_id',
                'service_type_code_snapshot',
                'service_type_name_snapshot',
                'service_type_variant_code_snapshot',
                'service_type_variant_label_snapshot',
                'priority',
                'description',
                'room_id',
                'building_name_snapshot',
                'floor_name_snapshot',
                'room_name_snapshot',
                'submitted_at',
            ]);
        });
    }
};
