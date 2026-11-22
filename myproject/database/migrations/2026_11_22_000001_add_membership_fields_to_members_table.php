<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table): void {
            $table->string('membership_number')->nullable()->unique()->after('id');
            $table->foreignId('membership_category_id')->nullable()->after('category')->constrained('membership_categories')->nullOnDelete();
            $table->foreignId('membership_level_id')->nullable()->after('level')->constrained('membership_levels')->nullOnDelete();
            $table->date('membership_expires_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table): void {
            $table->dropForeign(['membership_category_id']);
            $table->dropForeign(['membership_level_id']);
            $table->dropUnique(['membership_number']);
            $table->dropColumn([
                'membership_number',
                'membership_category_id',
                'membership_level_id',
                'membership_expires_at',
            ]);
        });
    }
};
