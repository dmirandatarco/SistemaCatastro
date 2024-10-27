<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tf_fichas_individuales', function (Blueprint $table) {
            $table->string('id_ficha',19)->unsigned();
            $table->foreign('id_ficha')->references('id_ficha')->on('tf_fichas')->onDelete('cascade')->onUpdate('cascade');
            $table->string('codi_uso',6)->unsigned();
            $table->foreign('codi_uso')->references('codi_uso')->on('tf_usos')->onDelete('cascade')->onUpdate('cascade');
            $table->string('cont_en',2)->nullable();
            $table->string('clasificacion',4)->nullable();
            $table->decimal('area_titulo',7,2)->nullable();
            $table->decimal('area_declarada',7,2)->nullable();
            $table->decimal('area_verificada',7,2)->nullable();
            $table->decimal('porc_bc_terr_legal',7,2)->nullable();
            $table->decimal('porc_bc_terr_fisc',7,2)->nullable();
            $table->decimal('porc_bc_const_legal',7,2)->nullable();
            $table->decimal('porc_bc_const_fisc',7,2)->nullable();
            $table->string('evaluacion',2)->nullable();
            $table->decimal('en_colindante',7,2)->nullable();
            $table->decimal('en_jardin_aislamiento',7,2)->nullable();
            $table->decimal('en_area_publica',7,2)->nullable();
            $table->decimal('en_area_intangible',7,2)->nullable();
            $table->string('cond_declarante',2)->nullable();
            $table->string('esta_llenado',1)->nullable();
            $table->integer('nume_habitantes')->nullable();
            $table->integer('nume_familias')->nullable();
            $table->string('mantenimiento',2)->nullable();
            $table->string('observaciones',500)->nullable();
            $table->string('estado_propiedad',10)->nullable();
            $table->string('nume_ficha',7)->nullable();
            $table->string('imagen_lote',250)->nullable();
            $table->string('imagen_plano',250)->nullable();
        });        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tf_fichas_individuales');
    }
};
