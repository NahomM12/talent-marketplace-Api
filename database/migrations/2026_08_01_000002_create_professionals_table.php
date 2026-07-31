<?php

declare(strict_types=1);

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
        Schema::create('professionals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('photo_path')->nullable();
            $table->string('role_title')->nullable();
            $table->text('bio')->nullable();
            $table->json('skills')->nullable();
            $table->foreignId('service_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professionals');
    }
};
