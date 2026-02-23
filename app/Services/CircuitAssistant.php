<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        if ($inventory->isEmpty() && $builds->isEmpty()) {
            return '';
        }

        $context = "\n\n## User's Workshop\n";
        $context .= "The user is logged in and has a tracked inventory and build projects. Use this info to give personalized advice.\n";

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

### Inventory Management
When the user asks you to fix inventory issues (mismatched names, wrong categories, missing items, kits that should cover sub-components, etc.):
- Explain what you'd recommend changing
- Be specific about which items to rename, recategorize, or adjust quantities for
- If a kit in inventory (like a robot kit) should cover multiple build parts (chassis, wheels, motors), tell the user which build parts are already covered by the kit
- You can suggest the user use the Workbench dashboard to make edits, or describe the exact changes needed

RULES;

        return $context;
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

        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'x-api-key' => config('franklinskey.api_key'),
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => config('franklinskey.model'),
                    'max_tokens' => config('franklinskey.max_tokens'),
                    'system' => $systemPrompt,
                    'messages' => $messages,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['content'][0]['text'] ?? 'I had trouble generating a response. Please try again.';
            }

            Log::error('Franklin\'s Key API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return 'I hit a snag connecting to my brain. Give it another try in a moment.';

        } catch (\Exception $e) {
            Log::error('Franklin\'s Key API exception', [
                'message' => $e->getMessage(),
            ]);

            return 'Something went wrong on my end. Please try again in a moment.';
        }
    }
}
