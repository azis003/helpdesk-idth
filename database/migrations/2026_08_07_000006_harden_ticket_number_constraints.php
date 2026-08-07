<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement(<<<'SQL'
            ALTER TABLE ticket_number_sequences
            ADD CONSTRAINT ticket_number_sequences_class_check
            CHECK (ticket_class IN ('INC', 'REQ', 'CHG'))
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE ticket_number_sequences
            ADD CONSTRAINT ticket_number_sequences_year_check
            CHECK (ticket_year BETWEEN 1000 AND 9999)
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE ticket_number_sequences
            ADD CONSTRAINT ticket_number_sequences_last_sequence_check
            CHECK (last_sequence BETWEEN 0 AND 99999)
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE tickets
            ADD CONSTRAINT tickets_ticket_class_check
            CHECK (ticket_class IS NULL OR ticket_class IN ('INC', 'REQ', 'CHG'))
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE tickets
            ADD CONSTRAINT tickets_ticket_year_check
            CHECK (ticket_year IS NULL OR ticket_year BETWEEN 1000 AND 9999)
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE tickets
            ADD CONSTRAINT tickets_ticket_number_format_check
            CHECK (
                ticket_number IS NULL
                OR ticket_number ~ '^(INC|REQ|CHG)-[0-9]{4}-[0-9]{5}$'
            )
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE tickets
            ADD CONSTRAINT tickets_ticket_sequence_range_check
            CHECK (ticket_sequence IS NULL OR ticket_sequence BETWEEN 1 AND 99999)
        SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE tickets DROP CONSTRAINT IF EXISTS tickets_ticket_sequence_range_check');
        DB::statement('ALTER TABLE tickets DROP CONSTRAINT IF EXISTS tickets_ticket_number_format_check');
        DB::statement('ALTER TABLE tickets DROP CONSTRAINT IF EXISTS tickets_ticket_year_check');
        DB::statement('ALTER TABLE tickets DROP CONSTRAINT IF EXISTS tickets_ticket_class_check');
        DB::statement('ALTER TABLE ticket_number_sequences DROP CONSTRAINT IF EXISTS ticket_number_sequences_last_sequence_check');
        DB::statement('ALTER TABLE ticket_number_sequences DROP CONSTRAINT IF EXISTS ticket_number_sequences_year_check');
        DB::statement('ALTER TABLE ticket_number_sequences DROP CONSTRAINT IF EXISTS ticket_number_sequences_class_check');
    }
};
