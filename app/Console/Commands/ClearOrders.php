<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Usage: php artisan orders:clear [--force]
     */
    protected $signature = 'orders:clear {--force : Run without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Delete ALL records from order_items, receipts, and orders tables';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will DELETE ALL records in order_items, receipts, and orders. Continue?')) {
                $this->info('Aborted.');
                return self::SUCCESS;
            }
        }

        try {
            Schema::disableForeignKeyConstraints();

            DB::transaction(function () {
                // Use TRUNCATE if supported, otherwise fallback to delete
                foreach (['order_items', 'receipts', 'orders'] as $table) {
                    try {
                        DB::table($table)->truncate();
                    } catch (\Throwable $e) {
                        // Fallback (e.g., SQLite):
                        DB::table($table)->delete();
                    }
                }
            });

            $this->info('Cleared: order_items, receipts, orders');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to clear tables: ' . $e->getMessage());
            return self::FAILURE;
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
}
