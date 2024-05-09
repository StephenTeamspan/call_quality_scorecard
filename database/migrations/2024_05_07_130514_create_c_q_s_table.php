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
        Schema::create('c_q_s', function (Blueprint $table) {
            $table->id();
            $table->string('employee_name')->nullable();
            $table->string('LOB')->nullable();
            $table->string('date_of_recording')->nullable();
            $table->string('workorder')->nullable();
            $table->string('type_of_call')->nullable();
            $table->string('auditor')->nullable();
            $table->timestamp('audit_date')->nullable();
            $table->timestamp('date_processed')->nullable();
            $table->timestamp('time_processed')->nullable();
            $table->longText('CTQ', 1000)->nullable();
            $table->string('call_summary')->nullable();
            $table->string('strengths')->nullable();
            $table->string('opportunities')->nullable();
            $table->string('comments')->nullable();
            $table->integer('score')->nullable();
            $table->string('status')->nullable();
            $table->longtext('call_recording', 1000)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('c_q_s');
    }
};
