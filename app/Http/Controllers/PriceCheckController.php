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

        $results = [
            'query' => $query,
            'microcenter' => $this->searchMicroCenter($query),
            'amazon' => $this->searchAmazon($query),
            'ebay' => $this->searchEbay($query),
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

            // Micro Center uses data-name and data-price attributes on product elements
            if (preg_match_all('/data-name="([^"]+)".*?data-price="([^"]+)"/s', $html, $matches, PREG_SET_ORDER)) {
                $seen = [];
                foreach ($matches as $match) {
                    $name = html_entity_decode(trim($match[1]));
                    $price = (float) $match[2];
                    // Skip banners, duplicates, and zero-price items
                    if ($price <= 0 || isset($seen[$name]) || stripos($name, 'Banner') !== false) continue;
                    $seen[$name] = true;
                    $items[] = [
                        'name' => $name,
                        'price' => '$' . number_format($price, 2),
                    ];
                    if (count($items) >= 3) break;
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

            // Amazon: extract dollar amounts from rendered HTML
            // Look for price patterns like $5.36 or $12.99
            if (preg_match_all('/\$(\d{1,3})\.(\d{2})/', $html, $priceMatches, PREG_SET_ORDER)) {
                $seen = [];
                foreach ($priceMatches as $match) {
                    $price = $match[1] . '.' . $match[2];
                    $pf = (float) $price;
                    // Filter out shipping costs, very small amounts, and duplicates
                    if ($pf >= 1.00 && $pf <= 500 && !isset($seen[$price])) {
                        $seen[$price] = true;
                        $items[] = ['name' => '', 'price' => '$' . $price];
                        if (count($items) >= 3) break;
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
        // eBay search results are fully JS-rendered, so we can only provide a search link
        $searchUrl = 'https://www.ebay.com/sch/i.html?_nkw=' . urlencode($query) . '&_sop=15&LH_BIN=1';
        return ['url' => $searchUrl, 'items' => []];
    }
}
