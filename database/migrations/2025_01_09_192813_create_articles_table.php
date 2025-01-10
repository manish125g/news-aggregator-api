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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('author')->index();
            $table->string('title');
            $table->string('source')->index()->nullable();
            $table->string('source_url')->nullable();
            $table->text('description');
            $table->string('keywords')->default('')->index();
            $table->string('category')->default('')->index();
            $table->text('image_url')->nullable();
            $table->longText('content');
            $table->timestamp('published_at')->index();
            $table->enum('status', ['draft', 'published', 'archived'])->default('published');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
