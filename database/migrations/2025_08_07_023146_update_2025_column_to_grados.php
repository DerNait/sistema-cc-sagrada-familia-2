<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('grado', function (Blueprint $table) {
            $table->renameColumn('2025', 'year');
        });
    }

    public function down()
    {
        Schema::table('grado', function (Blueprint $table) {
            $table->renameColumn('year', '2025');
        });
    }
};
