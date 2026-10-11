<?php

namespace App\Console\Commands;

use App\Models\Quotation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Removes quotation PDFs stored on the old public disk. They are regenerated on
 * the private disk the next time a quotation is opened or downloaded.
 */
class PurgeLegacyQuotationPdfs extends Command
{
    protected $signature = 'quotations:purge-legacy-pdfs {--force : Actually delete the files}';

    protected $description = 'Delete quotation PDFs left on the public disk (they are regenerated on demand)';

    public function handle(): int
    {
        $files = Storage::disk('public')->files('quotations');
        $count = count($files);

        if (!$this->option('force')) {
            $this->info("{$count} legacy PDF(s) found. Re-run with --force to delete them.");
            return self::SUCCESS;
        }

        if ($count > 0) {
            Storage::disk('public')->delete($files);
        }

        Quotation::withoutGlobalScopes()
            ->where('pdf_path', 'like', 'storage/%')
            ->update(['pdf_path' => null]);

        $this->info("Deleted {$count} legacy PDF(s). They will be regenerated on the private disk when needed.");

        return self::SUCCESS;
    }
}
