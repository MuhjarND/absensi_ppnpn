<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecurityShiftWeeklySchedulesTable extends Migration
{
    public function up()
    {
        Schema::create('security_shift_weekly_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('cascade');
            $table->unsignedTinyInteger('day_of_week'); // 0=Minggu, 1=Senin, ... 6=Sabtu
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['user_id', 'day_of_week']);
            $table->index(['user_id', 'shift_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('security_shift_weekly_schedules');
    }
}
