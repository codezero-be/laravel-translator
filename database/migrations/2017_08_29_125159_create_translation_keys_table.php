<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTranslationKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('translation_keys', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('file_id');
            $table->string('key');
            $table->unique(['file_id', 'key']);
            $table->text('translations')->nullable();
            $table->boolean('is_html')->default(false);
            $table->timestamps();

            $table->foreign('file_id')
                ->references('id')
                ->on('translation_files')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('translation_keys');
    }
}
