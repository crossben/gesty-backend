<?php

namespace App\Jobs;

use App\Models\AIReport;
use App\Models\SchoolClass;
use App\Services\AIService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AnalyzeClassJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $classId,
        protected array $studentsData
    ) {}

    public function handle(AIService $aiService): void
    {
        $class = SchoolClass::findOrFail($this->classId);
        $result = $aiService->analyzeClass($this->classId, $this->studentsData);

        if ($result) {
            AIReport::create([
                'school_id' => $class->school_id,
                'class_id' => $this->classId,
                'type' => 'analysis',
                'report' => $result['insights'] ?? [],
                'recommendations' => $result['recommendations'] ?? [],
            ]);
        }
    }
}
