<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->string('phone')->nullable()->after('name');
            $table->string('role')->nullable()->after('phone');
        });

        // Make user_id nullable
        Schema::table('staff', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        // Backfill name/phone/role from existing user records
        DB::table('staff')->get()->each(function ($s) {
            if (!$s->user_id) return;

            $user = DB::table('users')->find($s->user_id);
            if (!$user) return;

            $role = DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('model_has_roles.model_id', $user->id)
                ->where('model_has_roles.model_type', 'App\\Models\\User')
                ->value('roles.name');

            DB::table('staff')->where('id', $s->id)->update([
                'name'  => $user->name,
                'phone' => $user->phone ?? null,
                'role'  => $role,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn(['name', 'phone', 'role']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
