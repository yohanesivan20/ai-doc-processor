<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Document;
use App\Services\OCRService;
use App\Services\AIExtractionService;

class ProcessDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $document;
    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    /**
     * Execute the job.
     */
    public function handle(OCRService $ocr, AIExtractionService $ai)
    {
        $doc = Document::find($this->document->id);

        try {
            $doc->update(['status' => 'processing']);

            $text = $ocr->extract($doc->file_path);

            // limit text biar tidak berat
            $text = substr($text, 0, 3000);

            $result = $ai->extract($text);

            $doc->update([
                'status' => 'done',
                'extracted_text' => $text,
                'result_json' => $result
            ]);

        } catch (\Throwable $e) {
            $doc->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
        }
    }
}
