<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AIExtractionService
{
    protected string $baseUrl;
    protected string $model;

    public function __construct()
    {
        $this->baseUrl = config('services.ollama.url');
        $this->model = config('services.ollama.model');
    }

    public function extract(string $text): array
    {
        $prompt = $this->buildPrompt($text);

        $response = Http::timeout(config('services.ollama.timeout'))
            ->post($this->baseUrl . '/api/generate', [
                'model' => $this->model,
                'prompt' => $prompt,
                'stream' => false
            ]);

        if (!$response->successful()) {
            return [
                'error' => 'Ollama request failed',
                'detail' => $response->body()
            ];
        }

        return $this->parseResponse($response->json()['response'] ?? '');
    }

    private function buildPrompt(string $text): string
    {
        return '
            You are a STRICT JSON generator.

            IMPORTANT RULES:
            - Output MUST be a valid JSON object
            - MUST start with { and end with }
            - DO NOT output text outside JSON
            - DO NOT output markdown or triple backticks
            - "items" MUST always be an array (minimum 1 item if found in text)
            - "amount", "subtotal", "tax", "total" MUST be plain integers or floats
            Example: 5000000 not "Rp 5,000,000"
            - Strip all currency symbols, commas, and dots from numbers

            FIELD MAPPING RULES:
            - "customer" = the buyer. Look for labels: "Bill To", "Billed To", "Customer", "Pembeli", "Kepada"
            - "vendor"   = the seller. Look for labels: "Vendor", "From", "Dari", "Penjual", "Supplier"
            - "tax"      = Look for labels: "Tax", "PPN", "VAT", "Pajak"
            - "subtotal" = Look for labels: "Subtotal", "Sub Total", "Sub-total"

            EXAMPLE OUTPUT:
            {
                "invoice_number": "INV-001",
                "date": "20 April 2026",
                "vendor": "PT Contoh",
                "customer": "PT Pembeli",
                "items": [
                    { "description": "Web Development Service", "amount": 5000000 },
                    { "description": "Hosting (1 year)", "amount": 1000000 }
                ],
                "subtotal": 6000000,
                "tax": 600000,
                "total": 6600000
            }

            Now extract from this TEXT and return JSON only:
            ' . $text . '
        ';
    }

    private function parseResponse(string $text): array
    {
        $text = trim($text);

        // 1️⃣ Strip markdown code blocks
        $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/\s*```$/i', '', $text);
        $text = trim($text);

        // 2️⃣ Strip backslash escapes
        $text = stripcslashes($text);

        // 3️⃣ Decode langsung
        $decoded = json_decode($text, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $this->normalizeResult($decoded);
        }

        // 4️⃣ Extract { ... }
        $start = strpos($text, '{');
        $end   = strrpos($text, '}');

        if ($start !== false && $end !== false && $end > $start) {
            $json    = substr($text, $start, $end - $start + 1);
            $decoded = json_decode($json, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $this->normalizeResult($decoded); // ✅ lewat normalizeResult
            }
        }

        // 5️⃣ Fallback wrap manual
        $wrapped = '{' . trim($text, " \t\n\r\0\x0B,") . '}';
        $decoded = json_decode($wrapped, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $this->normalizeResult($decoded); // ✅ lewat normalizeResult
        }

        return [
            'error' => 'JSON decode failed: ' . json_last_error_msg(),
            'raw'   => $text
        ];
    }

    /**
     * Pastikan semua field ada & items selalu array
     */
    private function normalizeResult(array $data): array
    {
        return [
            'invoice_number' => $data['invoice_number'] ?? null,
            'date'           => $data['date']           ?? null,
            'vendor'         => $data['vendor']         ?? null,
            'customer'       => $data['customer']       ?? null,
            'items'          => $this->normalizeItems($data['items'] ?? []),
            'subtotal'       => $this->sanitizeNumber($data['subtotal'] ?? null), // ✅ fix
            'tax'            => $this->sanitizeNumber($data['tax']      ?? null), // ✅ fix
            'total'          => $this->sanitizeNumber($data['total']    ?? null), // ✅ fix
        ];
    }

    /**
     * Pastikan items valid array of objects
     */
    private function normalizeItems(mixed $items): array
    {
        if (!is_array($items)) {
            return [];
        }

        return array_map(function ($item) {
            return [
                'description' => $item['description'] ?? null,
                'amount'      => isset($item['amount']) ? (float) $item['amount'] : null,
            ];
        }, $items);
    }

    /**
     * Konversi "Rp 6,600,000" atau "6.600.000" → 6600000.0
     */
    private function sanitizeNumber(mixed $value): ?float
    {
        if (is_null($value)) return null;
        if (is_numeric($value)) return (float) $value;

        // Hapus semua karakter non-numerik kecuali titik & koma
        $cleaned = preg_replace('/[^\d,.]/', '', (string) $value);

        // Handle format 6,600,000 (koma sebagai thousand separator)
        if (preg_match('/^\d{1,3}(,\d{3})+$/', $cleaned)) {
            $cleaned = str_replace(',', '', $cleaned);
        }

        // Handle format 6.600.000 (titik sebagai thousand separator)
        if (preg_match('/^\d{1,3}(\.\d{3})+$/', $cleaned)) {
            $cleaned = str_replace('.', '', $cleaned);
        }

        return is_numeric($cleaned) ? (float) $cleaned : null;
    }
}