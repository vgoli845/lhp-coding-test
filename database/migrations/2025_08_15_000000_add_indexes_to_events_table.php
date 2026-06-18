<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Date filtering and ordering both run against the event start time.
            $table->index('created_time');
            // City filtering uses a bounding box on the coordinates.
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['created_time']);
            $table->dropIndex(['latitude', 'longitude']);
        });
    }
};
