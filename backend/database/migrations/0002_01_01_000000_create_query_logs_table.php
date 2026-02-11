<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('query_logs', function (Blueprint $table) {
            $table->id();
            $table->string('search_type', 10);   // 'people' or 'films'
            $table->string('search_query', 100);
            $table->float('duration_ms');
            $table->integer('result_count')->default(0);
            $table->boolean('cached')->default(false);
            $table->boolean('is_error')->default(false);
            $table->timestamp('created_at')->nullable();

            $table->index(['search_type', 'search_query']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('query_logs');
    }
};
