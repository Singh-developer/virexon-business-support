<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assertion_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('subject');
            $table->text('greeting');
            $table->text('body');
            $table->text('closing');
            $table->string('signature_name')->nullable();
            $table->string('signature_designation')->nullable();
            $table->string('signature_company')->nullable();
            $table->string('signature_image')->nullable(); // uploaded signature image path
            $table->json('dynamic_fields')->nullable();    // dynamic key-value pairs
            $table->string('pdf_path')->nullable();
            $table->string('status')->default('draft'); // draft, sent
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assertion_letters');
    }
};
