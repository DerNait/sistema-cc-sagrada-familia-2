<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('module_permission_id')->nullable();
            $table->string('name');
            $table->integer('order');
            $table->string('route')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
        
            $table->foreign('module_permission_id')
                  ->references('id')->on('modulos_permisos')
                  ->restrictOnDelete()->cascadeOnUpdate();
        
            $table->foreign('parent_id')
                  ->references('id')->on('menus')
                  ->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
