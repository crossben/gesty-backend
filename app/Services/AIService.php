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
        $this->baseUrl = config('services.ai.url', 'http://localhost:8008');
        $this->apiKey = config('services.ai.key', '');
    }

    public function analyzeClass(string $class_id, array $studentsData)
    {
        try {
            Log::info('AI Analysis Request for class: ' . $class_id);
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
            ])->post($this->baseUrl . '/ai-analysis/analyze-class', [
                'class_id' => $class_id,
                'students' => $studentsData,
            ]);

            Log::info('AI Analysis Response size: ' . strlen($response->body()));
            Log::debug('AI Analysis Response body: ' . $response->body());

            return $response->json();
        } catch (\Exception $e) {
            Log::error('AI Analysis Error: ' . $e->getMessage());
            return null;
        }
    }

    public function generateAcademicItem(array $data)
    {
        try {
            Log::info('AI Generation Request:', $data);
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
            ])->post($this->baseUrl . '/generation/generate-academic', $data);

            Log::info('AI Generation Response:', $response->json() ?? []);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('AI Generation Error: ' . $e->getMessage());
            return null;
        }
    }

    public function parseExcel($filePath)
    {
        try {
            Log::info('AI Excel Parsing Request');
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
            ])->attach('file', file_get_contents($filePath), basename($filePath))
              ->post($this->baseUrl . '/parsing/parse-excel');

            Log::info('AI Excel Parsing Response: ' . $response->status());

            return $response->json();
        } catch (\Exception $e) {
            Log::error('AI Excel Parsing Error: ' . $e->getMessage());
            return null;
        }
    }
}
