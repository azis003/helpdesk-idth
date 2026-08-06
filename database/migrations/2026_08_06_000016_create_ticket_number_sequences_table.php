<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_number_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_class', 3);
            $table->unsignedSmallInteger('ticket_year');
            $table->unsignedInteger('last_sequence')->default(0);
            $table->timestamps();
            $table->unique(['ticket_class', 'ticket_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_number_sequences');
    }
};
