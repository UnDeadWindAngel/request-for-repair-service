<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('clientName');
            $table->string('phone');
            $table->string('address');
            $table->text('problemText');
            $table->string('status');
            $table->unsignedBigInteger('assignedTo')->nullable();
            $table->timestamps();

            // Внешний ключ на таблицу users
            $table->foreign('assignedTo')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // Индексы (ускоряют запросы с фильтрацией по этим полям)
            $table->index('assignedTo');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
