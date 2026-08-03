<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('territories')) {
            Schema::create('territories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
                $table->string('name', 100);
                $table->string('region', 100)->nullable();
                $table->integer('property_count')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('territories');
    }
};
