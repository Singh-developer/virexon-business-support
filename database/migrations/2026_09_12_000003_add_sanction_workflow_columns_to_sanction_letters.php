<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sanction_letters', function (Blueprint $table) {
            $table->timestamp('downloaded_at')->nullable()->after('sent_at');
            $table->string('signed_pdf_path')->nullable()->after('downloaded_at');
            $table->timestamp('signed_pdf_uploaded_at')->nullable()->after('signed_pdf_path');
            $table->unsignedInteger('signed_pdf_upload_count')->default(0)->after('signed_pdf_uploaded_at');
            $table->string('review_status', 30)->default('pending')->index()->after('signed_pdf_upload_count');
            $table->timestamp('reviewed_at')->nullable()->after('review_status');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('reviewed_at');
            $table->text('review_comment')->nullable()->after('reviewed_by');

            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sanction_letters', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn([
                'downloaded_at',
                'signed_pdf_path',
                'signed_pdf_uploaded_at',
                'signed_pdf_upload_count',
                'review_status',
                'reviewed_at',
                'reviewed_by',
                'review_comment',
            ]);
        });
    }
};