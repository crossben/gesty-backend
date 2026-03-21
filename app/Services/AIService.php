<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.ai.url', 'http://localhost:8000');
        $this->apiKey = config('services.ai.key', '');
    }

    public function analyzeClass(string $classId, array $studentsData)
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
            ])->post($this->baseUrl . '/analyze-class', [
                'class_id' => $classId,
                'students' => $studentsData,
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('AI Analysis Error: ' . $e->getMessage());
            return null;
        }
    }

    public function generateAcademicItem(array $data)
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
            ])->post($this->baseUrl . '/generate-academic', $data);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('AI Generation Error: ' . $e->getMessage());
            return null;
        }
    }

    public function parseExcel($filePath)
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
            ])->attach('file', file_get_contents($filePath), basename($filePath))
              ->post($this->baseUrl . '/parse-excel');

            return $response->json();
        } catch (\Exception $e) {
            Log::error('AI Excel Parsing Error: ' . $e->getMessage());
            return null;
        }
    }
}
