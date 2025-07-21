<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->text('excerpt')->nullable();
            $table->string('featured_image')->nullable();
            $table->boolean('is_draft')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            //$table->index(['title', 'content'], 'fulltext_index'); // Tambahkan fulltext index
            //$table->fullText(['title', 'content']); // Untuk MySQL/InnoDB
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
};
