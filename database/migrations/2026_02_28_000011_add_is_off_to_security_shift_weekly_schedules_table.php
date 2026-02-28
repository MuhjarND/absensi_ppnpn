<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddIsOffToSecurityShiftWeeklySchedulesTable extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE security_shift_weekly_schedules MODIFY shift_id BIGINT UNSIGNED NULL');

        if (!Schema::hasColumn('security_shift_weekly_schedules', 'is_off')) {
            Schema::table('security_shift_weekly_schedules', function (Blueprint $table) {
                $table->boolean('is_off')->default(false)->after('shift_id');
            });
        }
    }

    public function down()
    {
        $fallbackShiftId = DB::table('shifts')->where('name', 'Reguler')->value('id');

        if (!$fallbackShiftId) {
            $fallbackShiftId = DB::table('shifts')->orderBy('id')->value('id');
        }

        if ($fallbackShiftId) {
            DB::table('security_shift_weekly_schedules')
                ->whereNull('shift_id')
                ->update(['shift_id' => $fallbackShiftId]);
        } else {
            DB::table('security_shift_weekly_schedules')
                ->whereNull('shift_id')
                ->delete();
        }

        if (Schema::hasColumn('security_shift_weekly_schedules', 'is_off')) {
            Schema::table('security_shift_weekly_schedules', function (Blueprint $table) {
                $table->dropColumn('is_off');
            });
        }

        DB::statement('ALTER TABLE security_shift_weekly_schedules MODIFY shift_id BIGINT UNSIGNED NOT NULL');
    }
}
