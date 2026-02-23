<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BraveSearch
{
    public static function needsSearch(string $message): bool
    {
        $message = strtolower($message);

        $patterns = [
            '/\b(what is|what are|what\'s|how to|how do|how does|tell me about|explain|look up|search for|find out|find me)\b/',
            '/\b(current|latest|newest|recent|today|now|price|cost|buy|where can|where to)\b/',
            '/\b(specs|specifications|features|compare|comparison|review|rating|best)\b/',
            '/\b(compatible|compatibility|work with|support|supported)\b/',
            '/\b(tutorial|guide|documentation|docs|manual|datasheet|pinout)\b/',
            '/\b(download|install|setup|driver|library|firmware|update)\b/',
            '/\b(who is|who are|who was|when was|when did|where is|where are)\b/',
            '/\b(recipe|ingredient|nutrition|calorie)\b/',
            '/\b(contents|what\'s in|included|comes with|package|kit contains)\b/',
            '/\b(can you look|can you find|can you search|can you check)\b/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        return false;
    }

    public static function search(string $query, int $maxResults = 3): ?array
    {
        $apiKey = config('services.brave.api_key');
        if (!$apiKey) {
            return null;
        }

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'X-Subscription-Token' => $apiKey,
                ])
                ->get('https://api.search.brave.com/res/v1/web/search', [
                    'q' => $query,
                    'count' => $maxResults,
                    'text_decorations' => false,
                    'search_lang' => 'en',
                ]);

            if (!$response->successful()) {
                Log::debug('Brave Search failed', ['status' => $response->status()]);
                return null;
            }

            $data = $response->json();
            $results = [];

            foreach ($data['web']['results'] ?? [] as $result) {
                $results[] = [
                    'title' => $result['title'] ?? '',
                    'description' => $result['description'] ?? '',
                    'url' => $result['url'] ?? '',
                ];
            }

            return $results;

        } catch (\Exception $e) {
            Log::debug('Brave Search error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    public static function cleanQuery(string $message): string
    {
        // Strip conversational fluff and meta-references to search/API
        $query = preg_replace('/\b(can you|could you|please|hey|hi|look up|search for|find out|find me|using brave|brave search|api|use the internet|use your|go online)\b/i', '', $message);
        $query = preg_replace('/\s+/', ' ', trim($query));
        return $query ?: $message;
    }

    public static function searchAndFormat(string $message, int $maxResults = 3): string
    {
        if (!self::needsSearch($message)) {
            return '';
        }

        $query = self::cleanQuery($message);
        $results = self::search($query, $maxResults);

        if (!$results || empty($results)) {
            return '';
        }

        $text = "\n\n--- LIVE WEB SEARCH RESULTS (fetched just now from the internet for this message) ---\n";
        foreach ($results as $r) {
            $text .= "- {$r['title']}: {$r['description']}\n  Source: {$r['url']}\n";
        }
        $text .= "--- END SEARCH RESULTS ---\n";

        return $text;
    }
}
