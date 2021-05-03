<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            // The user who created the project.
            $table->unsignedBigInteger('user_id');
            // The url where the widget will be included. All will be allowed if left empty.
            $table->string('url')->nullable();
            // The name of the project.
            $table->string('name');
            // If it's a premium subscription or not.
            $table->boolean('is_premium');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
    }
}