<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('comments');
    }

    public function down(): void
    {
        // The original comments table was created in the helpdesk migration.
        // Re-creating it here is intentionally omitted — restore from your backup.
    }
};
