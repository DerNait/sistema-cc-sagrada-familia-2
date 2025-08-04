<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('estudiante_notas', function (Blueprint $table) {
            $table->text('comentario')->nullable()->after('nota');
        });
    }

    public function down()
    {
        Schema::table('estudiante_notas', function (Blueprint $table) {
            $table->dropColumn('comentario');
        });
    }
};