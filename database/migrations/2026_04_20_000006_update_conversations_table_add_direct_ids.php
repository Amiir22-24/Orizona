<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('conversations', 'client_id')) {
                $table->foreignId('client_id')->nullable()->after('property_id')->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('conversations', 'agent_id')) {
                $table->foreignId('agent_id')->nullable()->after('client_id')->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('conversations', 'admin_id')) {
                $table->foreignId('admin_id')->nullable()->after('agent_id')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('conversations', 'status')) {
                $table->enum('status', ['active', 'closed'])->default('active')->after('admin_id');
            }
            if (!Schema::hasColumn('conversations', 'last_message')) {
                $table->text('last_message')->nullable()->after('status');
            }
            if (!Schema::hasColumn('conversations', 'last_message_at')) {
                $table->timestamp('last_message_at')->nullable()->after('last_message');
            }
            if (!Schema::hasColumn('conversations', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('last_message_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn(['client_id', 'agent_id', 'admin_id', 'status', 'last_message', 'last_message_at', 'closed_at']);
        });
    }
};
