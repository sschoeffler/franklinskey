<?php

namespace App\Services;

use App\Models\Build;
use App\Models\BuildPart;
use App\Models\InventoryItem;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CircuitAssistant
{
    public function getSystemPrompt(Project $project, ?User $user = null): string
    {
        $boardContext = $project->board_type
            ? "The user is working with a {$project->board_type}."
            : "The user hasn't specified their board yet. Ask what board they have (Arduino Uno, ESP32, Raspberry Pi Pico, etc.) when it becomes relevant.";

        $inventoryContext = '';
        if ($user) {
            $inventoryContext = $this->buildInventoryContext($user);
        }

        return <<<PROMPT
You are Franklin's Key — a patient, encouraging circuit-building assistant for beginners.

PROJECT: "{$project->name}"
{$boardContext}

## Your Personality
- Warm, patient, and encouraging — like a friendly mentor in a maker space
- Never condescending. Treat every question as a good question
- Use casual, clear language. Avoid jargon unless you explain it immediately
- Celebrate small wins ("Nice! That LED is about to light up.")

## How You Give Wiring Instructions
- Always use plain English: "Connect the red wire from the 5V pin on your Arduino to the + rail on the breadboard"
- Reference physical appearance: "the long leg of the LED" not "the anode"
- Give ONE step at a time when the user is new, or numbered steps for a full setup
- Always specify wire colors when possible (red for power, black for ground, etc.)
- Include safety notes naturally: "Make sure your board is unplugged while wiring"

## Code Handling — CRITICAL
- You write Arduino/ESP32/MicroPython code for the user, but it should be INVISIBLE in the conversation
- Wrap ALL code blocks in special markers so the UI can hide them:
  <!--CODE_START-->
  ```cpp
  // actual code here
  ```
  <!--CODE_END-->
- In your visible text, say things like "I've prepared the code for you" or "The code is ready to upload"
- NEVER show raw code outside the markers. The user doesn't want to see code — they want their project to work
- If the user specifically asks to see the code, you can mention it's available but still use the markers

## Image Analysis
- When the user sends a photo, identify components, wiring, and potential issues
- Be specific: "I can see an Arduino Uno, a breadboard, and what looks like a soil moisture sensor"
- If you spot wiring issues: "I notice the red wire on pin 7 — that should go to pin 5 for this to work"
- If the image is unclear, ask for a better angle rather than guessing

## Safety
- Always warn about: connecting power before wiring is complete, short circuits, exceeding voltage ratings
- If a project could involve mains electricity (120V/240V), firmly refuse and explain the danger
- Keep component voltages in safe ranges (3.3V/5V logic level projects only)

## Web Search — IMPORTANT
This application has a built-in web search API. The backend automatically searches the web for every user message and appends real-time search results to the end of this system prompt. If you see a "LIVE WEB SEARCH RESULTS" section below, those are REAL results fetched RIGHT NOW from the internet — use them confidently.
- DO NOT say "I can't browse the web" or "I don't have internet access" — that is incorrect in this application.
- DO NOT say search results "would need to be injected" — they ARE injected automatically.
- Present search results as your own knowledge: "The SunFounder GalaxyRVR kit includes..." not "According to the search results..."
- If no search results appear below, it means the query didn't trigger a search — but you still have the capability.

## Scope
- Arduino (Uno, Nano, Mega), ESP32, ESP8266, Raspberry Pi Pico
- Common sensors, LEDs, motors, displays, buzzers, relays (low-voltage only)
- Beginner to intermediate projects
- If asked about something outside scope, be honest: "That's beyond what I can help with safely"
{$inventoryContext}
PROMPT;
    }

    private function buildInventoryContext(User $user): string
    {
        $inventory = $user->inventoryItems()->orderBy('category')->orderBy('name')->get();
        $builds = $user->builds()->with('parts')->get();

        $context = "\n\n## User's Workshop\n";
        $context .= "The user is logged in and has a tracked inventory and build projects. Use this info to give personalized advice.\n";

        if ($inventory->isEmpty() && $builds->isEmpty()) {
            $context .= "\nThe user's inventory and builds are currently empty. Offer to help them add items as you discuss components.\n";
        }

        if ($inventory->isNotEmpty()) {
            $context .= "\n### Inventory ({$inventory->count()} items)\n";
            foreach ($inventory as $item) {
                $line = "- {$item->name} (qty: {$item->quantity}, category: {$item->category})";
                if ($item->description) {
                    $line .= " — {$item->description}";
                }
                $context .= $line . "\n";
            }
        }

        if ($builds->isNotEmpty()) {
            $context .= "\n### Build Projects\n";
            foreach ($builds as $build) {
                $readiness = $build->readiness;
                $context .= "\n**{$build->name}** ({$readiness['ready']}/{$readiness['total']} parts ready)\n";
                if ($build->description) {
                    $context .= "{$build->description}\n";
                }
                $context .= "Parts needed:\n";
                foreach ($build->parts as $part) {
                    $opt = $part->is_optional ? ' (optional)' : '';
                    $context .= "  - {$part->name} x{$part->quantity_needed}{$opt}\n";
                }
            }
        }

        $context .= <<<RULES

### Workshop Management — You Have Tools!
You can directly modify the user's inventory, builds, and projects using the provided tools. You don't need to tell them to "go to the dashboard" — just do it for them when they ask.

**Guidelines:**
- When the user mentions they have parts, bought something, or received a shipment — offer to add items to their inventory
- When discussing a project's parts list, offer to create a build and add the parts
- Always confirm before REMOVING or DELETING items — but adding is fine without asking
- After making changes, briefly confirm what was done (e.g. "Done! Added 5x Red LEDs to your inventory.")
- Use your best judgment on categories — boards, sensors, actuators, displays, cameras, wires, power, storage, enclosures, or misc
- If a fuzzy match fails, tell the user the exact name wasn't found and ask them to clarify

RULES;

        return $context;
    }

    public function getTools(): array
    {
        return [
            [
                'name' => 'add_inventory_item',
                'description' => 'Add a new item to the user\'s parts inventory. Use when they say they bought, received, or have a component.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string', 'description' => 'Name of the component (e.g. "Arduino Uno", "Red LED 5mm")'],
                        'quantity' => ['type' => 'integer', 'description' => 'How many they have. Defaults to 1.'],
                        'category' => ['type' => 'string', 'description' => 'Category: boards, sensors, actuators, displays, cameras, wires, power, storage, enclosures, or misc'],
                        'description' => ['type' => 'string', 'description' => 'Brief description of the item'],
                        'notes' => ['type' => 'string', 'description' => 'Any additional notes'],
                    ],
                    'required' => ['name'],
                ],
            ],
            [
                'name' => 'update_inventory_item',
                'description' => 'Update an existing inventory item (change quantity, category, description, or notes). Finds items by fuzzy name match.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'item_name' => ['type' => 'string', 'description' => 'Name (or partial name) of the item to update'],
                        'quantity' => ['type' => 'integer', 'description' => 'New quantity'],
                        'category' => ['type' => 'string', 'description' => 'New category'],
                        'description' => ['type' => 'string', 'description' => 'New description'],
                        'notes' => ['type' => 'string', 'description' => 'New notes'],
                    ],
                    'required' => ['item_name'],
                ],
            ],
            [
                'name' => 'remove_inventory_item',
                'description' => 'Remove an item from inventory entirely. Finds by fuzzy name match. Always confirm with the user before calling this.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'item_name' => ['type' => 'string', 'description' => 'Name (or partial name) of the item to remove'],
                    ],
                    'required' => ['item_name'],
                ],
            ],
            [
                'name' => 'create_build',
                'description' => 'Create a new build project (a list of parts needed for a circuit/project).',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string', 'description' => 'Name for the build (e.g. "LED Matrix", "Weather Station")'],
                        'description' => ['type' => 'string', 'description' => 'Brief description of what this build does'],
                    ],
                    'required' => ['name'],
                ],
            ],
            [
                'name' => 'add_build_part',
                'description' => 'Add a required part to a build project. Finds the build by fuzzy name match.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'build_name' => ['type' => 'string', 'description' => 'Name (or partial name) of the build to add the part to'],
                        'part_name' => ['type' => 'string', 'description' => 'Name of the part needed (e.g. "Red LED 5mm")'],
                        'quantity_needed' => ['type' => 'integer', 'description' => 'How many are needed. Defaults to 1.'],
                        'category' => ['type' => 'string', 'description' => 'Part category: boards, sensors, actuators, displays, cameras, wires, power, storage, enclosures, or misc'],
                        'is_optional' => ['type' => 'boolean', 'description' => 'Whether this part is optional. Defaults to false.'],
                    ],
                    'required' => ['build_name', 'part_name'],
                ],
            ],
            [
                'name' => 'remove_build_part',
                'description' => 'Remove a part from a build project. Finds both the build and part by fuzzy name match. Always confirm with the user first.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'build_name' => ['type' => 'string', 'description' => 'Name (or partial name) of the build'],
                        'part_name' => ['type' => 'string', 'description' => 'Name (or partial name) of the part to remove'],
                    ],
                    'required' => ['build_name', 'part_name'],
                ],
            ],
            [
                'name' => 'create_project',
                'description' => 'Create a new chat project (conversation workspace). Use when the user wants to start a new project topic.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string', 'description' => 'Name for the project (e.g. "Plant Watering System")'],
                        'board_type' => ['type' => 'string', 'description' => 'Board type if known (e.g. "Arduino Uno", "ESP32")'],
                    ],
                    'required' => ['name'],
                ],
            ],
            [
                'name' => 'rename_project',
                'description' => 'Rename an existing chat project. Finds by fuzzy name match.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'current_name' => ['type' => 'string', 'description' => 'Current name (or partial name) of the project to rename'],
                        'new_name' => ['type' => 'string', 'description' => 'The new name for the project'],
                    ],
                    'required' => ['current_name', 'new_name'],
                ],
            ],
        ];
    }

    public function executeTool(string $toolName, array $input, User $user): string
    {
        try {
            return match ($toolName) {
                'add_inventory_item' => $this->toolAddInventoryItem($input, $user),
                'update_inventory_item' => $this->toolUpdateInventoryItem($input, $user),
                'remove_inventory_item' => $this->toolRemoveInventoryItem($input, $user),
                'create_build' => $this->toolCreateBuild($input, $user),
                'add_build_part' => $this->toolAddBuildPart($input, $user),
                'remove_build_part' => $this->toolRemoveBuildPart($input, $user),
                'create_project' => $this->toolCreateProject($input, $user),
                'rename_project' => $this->toolRenameProject($input, $user),
                default => "Error: Unknown tool '{$toolName}'",
            };
        } catch (\Exception $e) {
            Log::error('Tool execution error', ['tool' => $toolName, 'error' => $e->getMessage()]);
            return "Error executing {$toolName}: " . $e->getMessage();
        }
    }

    private function fuzzyMatchItem(User $user, string $name): ?InventoryItem
    {
        $items = $user->inventoryItems()
            ->where('name', 'like', '%' . $name . '%')
            ->orderByRaw('LENGTH(name) ASC')
            ->get();

        if ($items->isEmpty()) {
            return null;
        }

        // Prefer exact match, then shortest name (most specific match)
        return $items->first(fn ($i) => strcasecmp($i->name, $name) === 0) ?? $items->first();
    }

    private function fuzzyMatchBuild(User $user, string $name): ?Build
    {
        $builds = $user->builds()
            ->where('name', 'like', '%' . $name . '%')
            ->orderByRaw('LENGTH(name) ASC')
            ->get();

        if ($builds->isEmpty()) {
            return null;
        }

        return $builds->first(fn ($b) => strcasecmp($b->name, $name) === 0) ?? $builds->first();
    }

    private function fuzzyMatchProject(User $user, string $name): ?Project
    {
        $projects = $user->projects()
            ->where('name', 'like', '%' . $name . '%')
            ->orderByRaw('LENGTH(name) ASC')
            ->get();

        if ($projects->isEmpty()) {
            return null;
        }

        return $projects->first(fn ($p) => strcasecmp($p->name, $name) === 0) ?? $projects->first();
    }

    private function toolAddInventoryItem(array $input, User $user): string
    {
        $name = $input['name'] ?? null;
        if (!$name) {
            return 'Error: name is required.';
        }

        $quantity = $input['quantity'] ?? 1;
        $category = $input['category'] ?? 'misc';

        $validCategories = array_keys(InventoryItem::categories());
        if (!in_array($category, $validCategories)) {
            $category = 'misc';
        }

        $item = InventoryItem::create([
            'user_id' => $user->id,
            'name' => $name,
            'quantity' => max(1, $quantity),
            'category' => $category,
            'description' => $input['description'] ?? null,
            'notes' => $input['notes'] ?? null,
        ]);

        return "Added {$item->quantity}x {$item->name} to inventory (category: {$item->category}).";
    }

    private function toolUpdateInventoryItem(array $input, User $user): string
    {
        $itemName = $input['item_name'] ?? null;
        if (!$itemName) {
            return 'Error: item_name is required.';
        }

        $item = $this->fuzzyMatchItem($user, $itemName);
        if (!$item) {
            return "Error: No inventory item matching '{$itemName}' found.";
        }

        $updates = [];
        if (isset($input['quantity'])) {
            $item->quantity = max(0, $input['quantity']);
            $updates[] = "quantity → {$item->quantity}";
        }
        if (isset($input['category'])) {
            $validCategories = array_keys(InventoryItem::categories());
            if (in_array($input['category'], $validCategories)) {
                $item->category = $input['category'];
                $updates[] = "category → {$item->category}";
            }
        }
        if (isset($input['description'])) {
            $item->description = $input['description'];
            $updates[] = 'description updated';
        }
        if (isset($input['notes'])) {
            $item->notes = $input['notes'];
            $updates[] = 'notes updated';
        }

        if (empty($updates)) {
            return "No changes specified for '{$item->name}'.";
        }

        $item->save();
        return "Updated '{$item->name}': " . implode(', ', $updates) . '.';
    }

    private function toolRemoveInventoryItem(array $input, User $user): string
    {
        $itemName = $input['item_name'] ?? null;
        if (!$itemName) {
            return 'Error: item_name is required.';
        }

        $item = $this->fuzzyMatchItem($user, $itemName);
        if (!$item) {
            return "Error: No inventory item matching '{$itemName}' found.";
        }

        $name = $item->name;
        $item->delete();
        return "Removed '{$name}' from inventory.";
    }

    private function toolCreateBuild(array $input, User $user): string
    {
        $name = $input['name'] ?? null;
        if (!$name) {
            return 'Error: name is required.';
        }

        $build = Build::create([
            'user_id' => $user->id,
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(6),
            'description' => $input['description'] ?? null,
            'status' => 'planning',
        ]);

        return "Created build '{$build->name}'. You can now add parts to it.";
    }

    private function toolAddBuildPart(array $input, User $user): string
    {
        $buildName = $input['build_name'] ?? null;
        $partName = $input['part_name'] ?? null;

        if (!$buildName || !$partName) {
            return 'Error: build_name and part_name are required.';
        }

        $build = $this->fuzzyMatchBuild($user, $buildName);
        if (!$build) {
            return "Error: No build matching '{$buildName}' found.";
        }

        $quantity = $input['quantity_needed'] ?? 1;
        $category = $input['category'] ?? 'misc';
        $isOptional = $input['is_optional'] ?? false;

        $validCategories = array_keys(InventoryItem::categories());
        if (!in_array($category, $validCategories)) {
            $category = 'misc';
        }

        $maxSort = $build->parts()->max('sort_order') ?? 0;

        $part = BuildPart::create([
            'build_id' => $build->id,
            'name' => $partName,
            'quantity_needed' => max(1, $quantity),
            'category' => $category,
            'is_optional' => $isOptional,
            'sort_order' => $maxSort + 1,
        ]);

        $optLabel = $isOptional ? ' (optional)' : '';
        return "Added {$part->quantity_needed}x {$part->name}{$optLabel} to build '{$build->name}'.";
    }

    private function toolRemoveBuildPart(array $input, User $user): string
    {
        $buildName = $input['build_name'] ?? null;
        $partName = $input['part_name'] ?? null;

        if (!$buildName || !$partName) {
            return 'Error: build_name and part_name are required.';
        }

        $build = $this->fuzzyMatchBuild($user, $buildName);
        if (!$build) {
            return "Error: No build matching '{$buildName}' found.";
        }

        $part = $build->parts()
            ->where('name', 'like', '%' . $partName . '%')
            ->orderByRaw('LENGTH(name) ASC')
            ->first();

        if (!$part) {
            return "Error: No part matching '{$partName}' in build '{$build->name}'.";
        }

        $name = $part->name;
        $part->delete();
        return "Removed '{$name}' from build '{$build->name}'.";
    }

    private function toolCreateProject(array $input, User $user): string
    {
        $name = $input['name'] ?? null;
        if (!$name) {
            return 'Error: name is required.';
        }

        $project = Project::create([
            'user_id' => $user->id,
            'session_id' => Str::uuid()->toString(),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(6),
            'board_type' => $input['board_type'] ?? null,
        ]);

        return "Created project '{$project->name}'" . ($project->board_type ? " with {$project->board_type}" : '') . '. The user can find it in their project list.';
    }

    private function toolRenameProject(array $input, User $user): string
    {
        $currentName = $input['current_name'] ?? null;
        $newName = $input['new_name'] ?? null;

        if (!$currentName || !$newName) {
            return 'Error: current_name and new_name are required.';
        }

        $project = $this->fuzzyMatchProject($user, $currentName);
        if (!$project) {
            return "Error: No project matching '{$currentName}' found.";
        }

        $oldName = $project->name;
        $project->name = $newName;
        $project->save();

        return "Renamed project '{$oldName}' to '{$newName}'.";
    }

    public function buildMessagesArray(Project $project, string $message, ?string $imagePath = null, ?string $imageMime = null): array
    {
        // Load conversation history (last 20 messages)
        $history = $project->messages()
            ->orderBy('created_at', 'asc')
            ->latest()
            ->take(20)
            ->get()
            ->sortBy('created_at')
            ->values();

        $messages = [];

        foreach ($history as $msg) {
            if ($msg->has_image && $msg->image_path && file_exists(storage_path('app/public/' . $msg->image_path))) {
                $imageData = base64_encode(file_get_contents(storage_path('app/public/' . $msg->image_path)));
                $messages[] = [
                    'role' => $msg->role,
                    'content' => [
                        [
                            'type' => 'image',
                            'source' => [
                                'type' => 'base64',
                                'media_type' => $msg->image_mime,
                                'data' => $imageData,
                            ],
                        ],
                        [
                            'type' => 'text',
                            'text' => $msg->content,
                        ],
                    ],
                ];
            } else {
                $messages[] = [
                    'role' => $msg->role,
                    'content' => $msg->content,
                ];
            }
        }

        // Add current user message
        if ($imagePath && $imageMime && file_exists(storage_path('app/public/' . $imagePath))) {
            $imageData = base64_encode(file_get_contents(storage_path('app/public/' . $imagePath)));
            $messages[] = [
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
                        'text' => $message,
                    ],
                ],
            ];
        } else {
            $messages[] = [
                'role' => 'user',
                'content' => $message,
            ];
        }

        return $messages;
    }

    public function chat(Project $project, string $message, ?string $imagePath = null, ?string $imageMime = null, ?User $user = null): string
    {
        $systemPrompt = $this->getSystemPrompt($project, $user);
        $systemPrompt .= BraveSearch::searchAndFormat($message);
        $messages = $this->buildMessagesArray($project, $message, $imagePath, $imageMime);

        // Only provide tools for authenticated users
        $tools = $user ? $this->getTools() : [];

        try {
            $maxToolRounds = 3;

            for ($round = 0; $round <= $maxToolRounds; $round++) {
                $payload = [
                    'model' => config('franklinskey.model'),
                    'max_tokens' => config('franklinskey.max_tokens'),
                    'system' => $systemPrompt,
                    'messages' => $messages,
                ];

                if (!empty($tools)) {
                    $payload['tools'] = $tools;
                }

                $response = Http::timeout(60)
                    ->withHeaders([
                        'x-api-key' => config('franklinskey.api_key'),
                        'anthropic-version' => '2023-06-01',
                        'content-type' => 'application/json',
                    ])
                    ->post('https://api.anthropic.com/v1/messages', $payload);

                if (!$response->successful()) {
                    Log::error('Franklin\'s Key API error', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    return 'I hit a snag connecting to my brain. Give it another try in a moment.';
                }

                $data = $response->json();

                // If stop reason is not tool_use, extract text and return
                if (($data['stop_reason'] ?? '') !== 'tool_use') {
                    return $this->extractTextFromContent($data['content'] ?? []);
                }

                // Handle tool use — execute each tool call and build result messages
                $assistantContent = $data['content'] ?? [];
                $toolResults = [];

                foreach ($assistantContent as $block) {
                    if (($block['type'] ?? '') === 'tool_use' && $user) {
                        $toolResult = $this->executeTool($block['name'], $block['input'] ?? [], $user);
                        $toolResults[] = [
                            'type' => 'tool_result',
                            'tool_use_id' => $block['id'],
                            'content' => $toolResult,
                        ];
                    }
                }

                if (empty($toolResults)) {
                    return $this->extractTextFromContent($assistantContent);
                }

                // Append assistant message (with tool_use blocks) and user message (with tool_results)
                $messages[] = ['role' => 'assistant', 'content' => $assistantContent];
                $messages[] = ['role' => 'user', 'content' => $toolResults];
            }

            // If we exhausted tool rounds, return whatever text we got
            return $this->extractTextFromContent($data['content'] ?? []);

        } catch (\Exception $e) {
            Log::error('Franklin\'s Key API exception', [
                'message' => $e->getMessage(),
            ]);

            return 'Something went wrong on my end. Please try again in a moment.';
        }
    }

    private function extractTextFromContent(array $content): string
    {
        foreach ($content as $block) {
            if (($block['type'] ?? '') === 'text' && !empty($block['text'])) {
                return $block['text'];
            }
        }

        return 'I had trouble generating a response. Please try again.';
    }
}
