<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('transactions', 'uploaded_by_user_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->after('id');
                $table->index('uploaded_by_user_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('transactions', 'uploaded_by_user_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropForeign(['uploaded_by_user_id']);
                $table->dropColumn('uploaded_by_user_id');
            });
        }
    }
};