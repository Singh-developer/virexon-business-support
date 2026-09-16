<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function addIfMissing(string $column, callable $callback): void
    {
        if (! Schema::hasColumn('sanction_letters', $column)) {
            Schema::table('sanction_letters', $callback);
        }
    }

    public function up(): void
    {
        $this->addIfMissing('process_fee', function (Blueprint $table) {
            $table->decimal('process_fee', 18, 2)->default(0)->after('status');
        });
        $this->addIfMissing('process_fee_status', function (Blueprint $table) {
            $table->string('process_fee_status')->default('unpaid')->after('process_fee');
        });
        $this->addIfMissing('process_fee_reference', function (Blueprint $table) {
            $table->string('process_fee_reference')->nullable()->unique()->after('process_fee_status');
        });
        $this->addIfMissing('process_fee_payment_id', function (Blueprint $table) {
            $table->string('process_fee_payment_id')->nullable()->after('process_fee_reference');
        });
        $this->addIfMissing('process_fee_paid_at', function (Blueprint $table) {
            $table->timestamp('process_fee_paid_at')->nullable()->after('process_fee_payment_id');
        });
        $this->addIfMissing('process_fee_gateway', function (Blueprint $table) {
            $table->string('process_fee_gateway')->nullable()->after('process_fee_paid_at');
        });
        $this->addIfMissing('process_fee_response', function (Blueprint $table) {
            $table->json('process_fee_response')->nullable()->after('process_fee_gateway');
        });
    }

    public function down(): void
    {
        $columns = [
            'process_fee',
            'process_fee_status',
            'process_fee_reference',
            'process_fee_payment_id',
            'process_fee_paid_at',
            'process_fee_gateway',
            'process_fee_response',
        ];

        $existing = array_filter($columns, fn (string $column) => Schema::hasColumn('sanction_letters', $column));

        if (! empty($existing)) {
            Schema::table('sanction_letters', function (Blueprint $table) use ($existing) {
                $table->dropColumn($existing);
            });
        }
    }
};