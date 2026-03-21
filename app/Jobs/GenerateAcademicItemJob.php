<?php

namespace App\Jobs;

use App\Models\AcademicItem;
use App\Services\AIService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateAcademicItemJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected array $inputData,
        protected bool $saveToDb = false
    ) {}

    public function handle(AIService $aiService): void
    {
        $result = $aiService->generateAcademicItem($this->inputData);

        if ($result && $this->saveToDb) {
            AcademicItem::create([
                'school_id' => $this->inputData['school_id'],
                'class_id' => $this->inputData['class_id'],
                'type' => $this->inputData['type'],
                'subject' => $this->inputData['subject'],
                'title' => $result['title'] ?? 'Generated Item',
                'description' => $result['content'] ?? '',
                'due_date' => now()->addDays(7),
                'max_score' => 20.00,
            ]);
        }
    }
}
