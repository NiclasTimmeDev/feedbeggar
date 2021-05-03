<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedbackPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feedback_posts', function (Blueprint $table) {
            $table->id();
            // The project the feedback belongs to.
            $table->unsignedBigInteger('project_id');

            // The type of the feedback. E.g 'bug'.
            $table->string('type');

            // The email of the user who submitted the feedback. Optional.
            $table->string('email')->nullable();

            // The text of the feedback.
            $table->longText('text');

            // The S3 link to the screenshot. Optional.
            $table->mediumText('screenshot')->nullable();

            // The browser name of the user who submitted the screenshot.
            $table->string('browser_name');

            // The major version of the used browser.
            $table->integer('browser_version');

            // The name of the operating system the user uses.
            $table->string('os_name');

            // A boolean indicating if the feedback is archived or not.
            $table->boolean('is_archived')->default(false);

            // The bucket a user assigned the feedback to.
            $table->unsignedBigInteger('bucket_id')->nullable();

            // The path the feedback was submitted from.
            $table->string('path')->nullable();

            // The timestamps.
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
        Schema::dropIfExists('feedback_posts');
    }
}