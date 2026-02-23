<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PriceCheckController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        if (!$query || strlen($query) < 2) {
            return response()->json(['error' => 'Query required'], 400);
        }

        // Add "electronic component" context for better results
        $componentQuery = $query;

        $results = [
            'query' => $query,
            'microcenter' => $this->searchMicroCenter($componentQuery),
            'amazon' => $this->searchAmazon($componentQuery),
            'ebay' => $this->searchEbay($componentQuery),
        ];

        return response()->json($results);
    }

    private function searchMicroCenter(string $query): array
    {
        $searchUrl = 'https://www.microcenter.com/search/search_results.aspx?Ntt=' . urlencode($query);

        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html',
                ])
                ->get($searchUrl);

            if (!$response->successful()) {
                return ['url' => $searchUrl, 'items' => []];
            }

            $html = $response->body();
            $items = [];

            // Micro Center product cards have data-price attributes and product info in structured HTML
            // Try to find price and product name patterns
            if (preg_match_all('/"price"\s*:\s*"?([\d.]+)"?/', $html, $priceMatches) &&
                preg_match_all('/data-name="([^"]+)"/', $html, $nameMatches)) {
                $count = min(count($priceMatches[1]), count($nameMatches[1]), 3);
                for ($i = 0; $i < $count; $i++) {
                    $items[] = [
                        'name' => html_entity_decode($nameMatches[1][$i]),
                        'price' => '$' . number_format((float)$priceMatches[1][$i], 2),
                    ];
                }
            }

            // Alternative: look for itemprice spans
            if (empty($items) && preg_match_all('/class="price"[^>]*>\s*\$?([\d,.]+)/', $html, $priceMatches2)) {
                if (preg_match_all('/class="pDescription"[^>]*>.*?<a[^>]*>([^<]+)/s', $html, $nameMatches2)) {
                    $count = min(count($priceMatches2[1]), count($nameMatches2[1]), 3);
                    for ($i = 0; $i < $count; $i++) {
                        $items[] = [
                            'name' => html_entity_decode(trim($nameMatches2[1][$i])),
                            'price' => '$' . $priceMatches2[1][$i],
                        ];
                    }
                }
            }

            return ['url' => $searchUrl, 'items' => $items];
        } catch (\Exception $e) {
            Log::debug('MicroCenter scrape failed', ['error' => $e->getMessage()]);
            return ['url' => $searchUrl, 'items' => []];
        }
    }

    private function searchAmazon(string $query): array
    {
        $searchUrl = 'https://www.amazon.com/s?k=' . urlencode($query);

        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ])
                ->get($searchUrl);

            if (!$response->successful()) {
                return ['url' => $searchUrl, 'items' => []];
            }

            $html = $response->body();
            $items = [];

            // Amazon prices appear in span.a-price-whole and span.a-price-fraction
            // Also try the JSON-LD or data attributes
            if (preg_match_all('/class="a-price"[^>]*>.*?<span[^>]*>.*?\$([\d,]+)<.*?(\d{2})/s', $html, $matches)) {
                $count = min(count($matches[1]), 3);
                for ($i = 0; $i < $count; $i++) {
                    $price = str_replace(',', '', $matches[1][$i]) . '.' . $matches[2][$i];
                    $items[] = ['name' => '', 'price' => '$' . $price];
                }
            }

            // Try simpler price pattern
            if (empty($items) && preg_match_all('/\$(\d+)\.(\d{2})</', $html, $simpleMatches)) {
                $seen = [];
                for ($i = 0; $i < count($simpleMatches[0]) && count($items) < 3; $i++) {
                    $price = $simpleMatches[1][$i] . '.' . $simpleMatches[2][$i];
                    if ((float)$price > 0.50 && (float)$price < 500 && !isset($seen[$price])) {
                        $items[] = ['name' => '', 'price' => '$' . $price];
                        $seen[$price] = true;
                    }
                }
            }

            return ['url' => $searchUrl, 'items' => $items];
        } catch (\Exception $e) {
            Log::debug('Amazon scrape failed', ['error' => $e->getMessage()]);
            return ['url' => $searchUrl, 'items' => []];
        }
    }

    private function searchEbay(string $query): array
    {
        $searchUrl = 'https://www.ebay.com/sch/i.html?_nkw=' . urlencode($query) . '&_sop=15&LH_BIN=1';

        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html',
                ])
                ->get($searchUrl);

            if (!$response->successful()) {
                return ['url' => $searchUrl, 'items' => []];
            }

            $html = $response->body();
            $items = [];

            // eBay search results: s-item__title and s-item__price
            if (preg_match_all('/class="s-item__title"[^>]*>(?:<span[^>]*>)?([^<]+)/s', $html, $nameMatches) &&
                preg_match_all('/class="s-item__price"[^>]*>\s*\$?([\d,.]+)/s', $html, $priceMatches)) {
                // Skip first result (often a header/placeholder)
                $offset = 0;
                if (isset($nameMatches[1][0]) && stripos($nameMatches[1][0], 'Shop on eBay') !== false) {
                    $offset = 1;
                }
                $count = min(count($priceMatches[1]) - $offset, count($nameMatches[1]) - $offset, 3);
                for ($i = 0; $i < $count; $i++) {
                    $name = html_entity_decode(trim($nameMatches[1][$i + $offset]));
                    $price = '$' . $priceMatches[1][$i + $offset];
                    if ($name && $name !== 'Shop on eBay') {
                        $items[] = ['name' => $name, 'price' => $price];
                    }
                }
            }

            return ['url' => $searchUrl, 'items' => $items];
        } catch (\Exception $e) {
            Log::debug('eBay scrape failed', ['error' => $e->getMessage()]);
            return ['url' => $searchUrl, 'items' => []];
        }
    }
}
