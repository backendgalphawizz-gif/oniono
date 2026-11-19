<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->string('login_id')->nullable()->unique()->after('name');
        });

        foreach (DB::table('admins')->whereNull('login_id')->get() as $admin) {
            DB::table('admins')->where('id', $admin->id)->update([
                'login_id' => $admin->id === 1 ? 'admin' : 'admin'.$admin->id,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('login_id');
        });
    }
};
