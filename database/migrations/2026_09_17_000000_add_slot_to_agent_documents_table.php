<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Support multiple files per document type:
     * - aadhaar: 2 files (front/back)
     * - pan_card, bank_proof: 1 file
     * - rest: multiple files
     */
    public function up(): void
    {
        Schema::table('agent_documents', function (Blueprint $table) {
            if (! Schema::hasColumn('agent_documents', 'slot')) {
                $table->string('slot', 30)->default('default')->after('document_type');
            }
        });

        // Backfill existing rows (in case default didn't apply on some drivers).
        \Illuminate\Support\Facades\DB::table('agent_documents')
            ->whereNull('slot')
            ->orWhere('slot', '')
            ->update(['slot' => 'default']);

        Schema::table('agent_documents', function (Blueprint $table) {
            // Ensure FK column stays indexed (MySQL requires an index for FK).
            // Add plain index first so dropping the old unique doesn't break the FK.
            try {
                $table->index(['user_id'], 'agent_docs_user_id_index');
            } catch (\Throwable $e) {
            }
            // Drop old single-file unique, allow multiple files per type via slot.
            try {
                $table->dropUnique(['user_id', 'document_type']);
            } catch (\Throwable $e) {
                // Index may already be dropped or named differently on some drivers.
            }
        });

        Schema::table('agent_documents', function (Blueprint $table) {
            try {
                $table->unique(['user_id', 'document_type', 'slot'], 'agent_docs_user_type_slot_unique');
            } catch (\Throwable $e) {
                // Unique may already exist.
            }
        });
    }

    public function down(): void
    {
        Schema::table('agent_documents', function (Blueprint $table) {
            try {
                $table->dropUnique('agent_docs_user_type_slot_unique');
            } catch (\Throwable $e) {
            }
        });

        // NOTE: down migration keeps slot column to avoid data loss,
        // but restores old unique only if no duplicates exist.
        try {
            Schema::table('agent_documents', function (Blueprint $table) {
                $table->unique(['user_id', 'document_type']);
            });
        } catch (\Throwable $e) {
        }
    }
};
