<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->boolean('scope_all_regions')->default(true)->after('is_active');
        });

        Schema::create('admin_geo_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->string('state', 100);
            $table->string('city', 100)->nullable();
            $table->timestamps();

            $table->index(['admin_id', 'state', 'city']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_geo_scopes');

        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('scope_all_regions');
        });
    }
};
