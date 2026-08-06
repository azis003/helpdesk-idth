<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_calendar_holidays', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_calendar_id')->constrained()->cascadeOnDelete();
            $table->date('holiday_date');
            $table->string('name', 150);
            $table->timestamps();
            $table->unique(['service_calendar_id', 'holiday_date']);
            $table->index('holiday_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_calendar_holidays');
    }
};
