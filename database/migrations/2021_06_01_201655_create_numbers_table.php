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
            $table->unsignedInteger('hundred');
            $table->unsignedInteger('ten');
            $table->unsignedInteger('unit');
            $table->unsignedInteger('first');
            $table->unsignedInteger('second');
            $table->unsignedInteger('third');
            $table->unsignedInteger('fourth');

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
