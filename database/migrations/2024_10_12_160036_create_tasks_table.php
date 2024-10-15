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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('type',['Bug','Feature','Improvment']);
            $table->enum('status',['Open','In progress','Completed','Blocked']);  
            $table->enum('priority',['Low','Medium','High'])->default('Medium');
            $table->date('due_date');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->cascadeOnDelete();
            $table->integer('depends_on')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
