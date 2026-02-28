<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropSecurityShiftSchedulesTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('security_shift_schedules');
    }

    public function down()
    {
        // Override harian dihapus permanen.
    }
}
