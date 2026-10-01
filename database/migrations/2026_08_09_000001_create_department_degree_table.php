<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentDegreeTable extends Migration
{
    public function up()
    {
        Schema::create('department_degree', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('degree_id');
            $table->timestamps();

            $table->unique(['department_id', 'degree_id']);

            $table->foreign('department_id')
                  ->references('id')->on('departments')
                  ->onDelete('cascade');

            $table->foreign('degree_id')
                  ->references('id')->on('degrees')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('department_degree');
    }
}
