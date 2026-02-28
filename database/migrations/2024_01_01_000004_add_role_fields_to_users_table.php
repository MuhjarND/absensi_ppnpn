<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained('roles')->onDelete('set null');
            $table->string('nip')->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->string('position')->nullable()->after('phone');
            $table->boolean('is_security')->default(false)->after('position');
            $table->foreignId('shift_id')->nullable()->after('is_security')->constrained('shifts')->onDelete('set null');
            $table->boolean('is_active')->default(true)->after('shift_id');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['shift_id']);
            $table->dropColumn(['role_id', 'nip', 'phone', 'position', 'is_security', 'shift_id', 'is_active']);
        });
    }
}
