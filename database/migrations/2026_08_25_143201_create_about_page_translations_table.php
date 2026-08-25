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
        Schema::create('about_page_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('about_page_id')->constrained('about_page')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->string('story_title');
            $table->text('story_description')->nullable();
            $table->string('vision_title')->nullable();
            $table->text('vision_description')->nullable();
            $table->string('mission_title')->nullable();
            $table->text('mission_description')->nullable();
            $table->timestamps();

            $table->unique(['about_page_id', 'language_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_page_translations');
    }
};
