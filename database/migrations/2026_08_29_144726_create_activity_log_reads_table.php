<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * État "lu / masqué" d'une activité, propre à chaque utilisateur.
 * Une activité peut être vue par plusieurs gérants/propriétaires d'une
 * même boulangerie ; chacun a son propre statut de lecture.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_log_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamps();

            $table->unique(['activity_log_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log_reads');
    }
};
