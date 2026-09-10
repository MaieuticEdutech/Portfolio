<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category');
            $table->string('project_type')->nullable();
            $table->string('year')->nullable();
            $table->string('accent_gradient_start')->nullable();
            $table->string('accent_gradient_mid')->nullable();
            $table->string('accent_gradient_end')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('tile_size')->default('med');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['is_published', 'category', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_clients');
    }
};
