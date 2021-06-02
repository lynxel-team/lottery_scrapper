<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNumbersTable extends Migration
{
    public function up()
    {
        Schema::create('numbers', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->date('ndate');
            $table->unsignedInteger('hundred')->nullable();
            $table->unsignedInteger('ten')->nullable();
            $table->unsignedInteger('unit')->nullable();
            $table->unsignedInteger('first')->nullable();
            $table->unsignedInteger('second')->nullable();
            $table->unsignedInteger('third')->nullable();
            $table->unsignedInteger('fourth')->nullable();

            $table->foreignId('section_id')
                ->constrained();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('numbers');
    }
}
