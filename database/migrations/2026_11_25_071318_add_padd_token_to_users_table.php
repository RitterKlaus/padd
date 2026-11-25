<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('padd_token', 40)->nullable()->unique();
        });

        DB::table('users')->whereNull('padd_token')->pluck('id')->each(
            fn (int $id) => DB::table('users')->where('id', $id)->update(['padd_token' => Str::random(40)]),
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['padd_token']);
            $table->dropColumn('padd_token');
        });
    }
};
