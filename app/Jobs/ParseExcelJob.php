<?php

namespace App\Jobs;

use App\Services\AIService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ParseExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $filePath,
        protected string $schoolId
    ) {}

    public function handle(AIService $aiService): void
    {
        $result = $aiService->parseExcel($this->filePath);

        if ($result) {
            // Logic to process the parsed data (students, grades, schedule)
            // This would involve looping through results and inserting into DB
            Log::info('Excel Parsed successfully for school: ' . $this->schoolId);
        }

        // Cleanup temporary file
        if (file_exists($this->filePath)) {
            unlink($this->filePath);
        }
    }
}
