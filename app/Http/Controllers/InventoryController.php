<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    public function index()
    {
        $items = Auth::user()->inventoryItems()->orderBy('name')->get();
        $categories = InventoryItem::categories();

        return response()->json([
            'items' => $items,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:50',
            'quantity' => 'nullable|integer|min:1|max:9999',
            'notes' => 'nullable|string|max:2000',
        ]);

        $item = Auth::user()->inventoryItems()->create([
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category ?? 'misc',
            'quantity' => $request->quantity ?? 1,
            'notes' => $request->notes,
        ]);

        return response()->json(['success' => true, 'item' => $item]);
    }

    public function update(Request $request, InventoryItem $item)
    {
        if ($item->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:200',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:50',
            'quantity' => 'sometimes|integer|min:0|max:9999',
            'notes' => 'nullable|string|max:2000',
        ]);

        $item->update($request->only(['name', 'description', 'category', 'quantity', 'notes']));

        return response()->json(['success' => true, 'item' => $item]);
    }

    public function destroy(InventoryItem $item)
    {
        if ($item->user_id !== Auth::id()) {
            abort(403);
        }

        $item->delete();

        return response()->json(['success' => true]);
    }

    public function scan(Request $request)
    {
        $request->validate([
            'image' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,webp',
            'context' => 'nullable|string|max:200', // 'receipt' or 'item'
        ]);

        $file = $request->file('image');
        $imagePath = $file->store('scans', 'public');
        $imageData = base64_encode(file_get_contents($file->getPathname()));
        $imageMime = $file->getMimeType();
        $context = $request->input('context', 'item');

        $prompt = $context === 'receipt'
            ? 'This is a receipt from an electronics/components store. Identify ALL electronic components, boards, sensors, wires, tools, and accessories listed. For each item, extract: the product name (simplified/generic name), a brief description, suggested category (one of: boards, sensors, actuators, displays, cameras, wires, power, storage, enclosures, misc), and quantity purchased. Return ONLY valid JSON as an array of objects with keys: name, description, category, quantity. Do not include non-electronic items like bags or general merchandise.'
            : 'Identify the electronic component(s) in this image. For each component visible, provide: the product name (simplified/generic name), a brief description of what it is and what it does, and a suggested category (one of: boards, sensors, actuators, displays, cameras, wires, power, storage, enclosures, misc). Return ONLY valid JSON as an array of objects with keys: name, description, category, quantity (default 1).';

        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'x-api-key' => config('franklinskey.api_key'),
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => config('franklinskey.model'),
                    'max_tokens' => 4096,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'image',
                                    'source' => [
                                        'type' => 'base64',
                                        'media_type' => $imageMime,
                                        'data' => $imageData,
                                    ],
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $prompt,
                                ],
                            ],
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $text = $response->json('content.0.text', '[]');

                // Extract JSON from response (may be wrapped in markdown code block)
                if (preg_match('/\[[\s\S]*\]/', $text, $matches)) {
                    $items = json_decode($matches[0], true);

                    if (is_array($items)) {
                        return response()->json([
                            'success' => true,
                            'items' => $items,
                            'image_url' => Storage::url($imagePath),
                        ]);
                    }
                }

                return response()->json([
                    'success' => false,
                    'error' => 'Could not parse component list from the image. Try a clearer photo.',
                ]);
            }

            Log::error('Scan API error', ['status' => $response->status(), 'body' => $response->body()]);
            return response()->json(['success' => false, 'error' => 'Failed to analyze image.']);

        } catch (\Exception $e) {
            Log::error('Scan exception', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Something went wrong analyzing the image.']);
        }
    }

    public function bulkAdd(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:200',
            'items.*.description' => 'nullable|string|max:1000',
            'items.*.category' => 'nullable|string|max:50',
            'items.*.quantity' => 'nullable|integer|min:1|max:9999',
            'source' => 'nullable|string|max:200',
            'scan_image' => 'nullable|string|max:500',
        ]);

        $source = $request->input('source');
        $scanImage = $request->input('scan_image');

        $existing = Auth::user()->inventoryItems()->get()->keyBy(fn($item) => strtolower(trim($item->name)));
        $result = [];

        foreach ($request->items as $itemData) {
            $key = strtolower(trim($itemData['name']));
            $qty = $itemData['quantity'] ?? 1;

            if ($existing->has($key)) {
                $item = $existing->get($key);
                $item->increment('quantity', $qty);
                if ($source && !$item->source) {
                    $item->update(['source' => $source]);
                }
                $item->refresh();
                $result[] = $item;
            } else {
                $item = Auth::user()->inventoryItems()->create([
                    'name' => $itemData['name'],
                    'description' => $itemData['description'] ?? null,
                    'category' => $itemData['category'] ?? 'misc',
                    'quantity' => $qty,
                    'source' => $source,
                    'scan_image' => $scanImage,
                ]);
                $existing->put($key, $item);
                $result[] = $item;
            }
        }

        return response()->json(['success' => true, 'items' => $result, 'merged' => true]);
    }

    public function mergeDuplicates()
    {
        $items = Auth::user()->inventoryItems()->orderBy('id')->get();
        $seen = [];
        $deleted = 0;

        foreach ($items as $item) {
            $key = strtolower(trim($item->name));

            if (isset($seen[$key])) {
                $seen[$key]->increment('quantity', $item->quantity);
                $item->delete();
                $deleted++;
            } else {
                $seen[$key] = $item;
            }
        }

        $inventory = Auth::user()->inventoryItems()->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'merged' => $deleted,
            'items' => $inventory,
        ]);
    }
}
