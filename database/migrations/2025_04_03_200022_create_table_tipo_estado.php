<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tipo_estado', function (Blueprint $table) {
            $table->id();
            $table->string('tipo',100);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tipo_estado');
    }
};