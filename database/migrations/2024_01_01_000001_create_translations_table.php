<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('table_name');
            $table->string('row_id');
            $table->string('locale', 10);
            $table->string('field');
            $table->text('value');

            $table->unique(['table_name', 'row_id', 'locale', 'field'], 'translations_unique');
            $table->index(['table_name', 'row_id'], 'translations_row_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
