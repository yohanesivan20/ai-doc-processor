<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OCRService
{
    public function extract(string $path): string
    {
        Log::info('OCR: Start extract', [
            'path' => $path
        ]);

        $fullPath = Storage::disk('public')->path($path);

        if (!file_exists($fullPath)) {
            Log::error('OCR: File not found', [
                'full_path' => $fullPath
            ]);

            throw new \Exception('File not found');
        }

        Log::info('OCR: File found', [
            'full_path' => $fullPath
        ]);

        $tesseract = config('services.tesseract.path');

        if (!$tesseract) {
            Log::error('OCR: Tesseract path not set in config');
            throw new \Exception('Tesseract path not configured');
        }

        $command = '"' . $tesseract . '" '
            . escapeshellarg($fullPath)
            . ' stdout -l eng --psm 6 2>&1';

        Log::info('OCR: Running command', [
            'command' => $command
        ]);

        $output = shell_exec($command);

        Log::info('OCR: Raw output', [
            'output' => $output
        ]);

        if (empty(trim($output))) {
            Log::error('OCR: Empty result from Tesseract');

            throw new \Exception('OCR failed or empty result');
        }

        $output = $this->cleanText($output);

        Log::info('OCR: Clean output', [
            'output' => $output
        ]);

        Log::info('OCR: Success');

        return $output;
    }

    /**
     * Clean OCR result biar AI gampang baca
     */
    private function cleanText(string $text): string
    {
        // remove excessive whitespace & newline
        $text = preg_replace('/\s+/', ' ', $text);

        // normalize spacing
        $text = trim($text);

        // optional: normalize currency format
        $text = str_replace(['Rp.', 'Rp'], 'Rp', $text);

        return $text;
    }
}