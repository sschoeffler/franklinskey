<?php

namespace Database\Seeders;

use App\Models\Build;
use App\Models\BuildPart;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BuildSeeder extends Seeder
{
    public function run(): void
    {
        // Create stub user
        $user = User::firstOrCreate(
            ['email' => 'sschoeffler@gmail.com'],
            [
                'name' => 'Steve',
                'password' => Hash::make(Str::random(32)),
            ]
        );

        // Build 1: Front Dashcam
        $frontDashcam = Build::create([
            'user_id' => $user->id,
            'name' => 'Front Dashcam',
            'slug' => 'front-dashcam',
            'description' => 'ESP32-CAM based dashcam for the front windshield. Continuous recording to microSD with loop overwrite.',
            'status' => 'planning',
            'sort_order' => 1,
            'instructions' => "# Front Dashcam Build\n\n## Overview\nA compact dashcam using the ESP32-CAM module. Records video to a microSD card with automatic loop recording (oldest files overwritten when full).\n\n## Wiring\n1. Insert microSD card into the ESP32-CAM's card slot\n2. Connect USB-to-serial adapter for programming:\n   - **5V** → ESP32-CAM 5V\n   - **GND** → ESP32-CAM GND\n   - **TX** → ESP32-CAM U0R\n   - **RX** → ESP32-CAM U0T\n3. Connect **GPIO 0** to **GND** for flashing mode\n\n## Power\n- Use a USB car charger (5V 2A minimum)\n- Connect via USB cable to the ESP32-CAM\n\n## Mounting\n- Use suction cup mount on windshield\n- Position behind rearview mirror for minimal obstruction\n- Route USB cable along headliner to 12V outlet\n\n## Software\n- Flash with Arduino IDE or PlatformIO\n- Camera resolution: 800x600 for balance of quality and file size\n- Recording format: MJPEG to .avi files\n- Loop recording: delete oldest file when SD card is >90% full",
        ]);

        $this->addParts($frontDashcam, [
            ['name' => 'ESP32-CAM', 'description' => 'ESP32 with OV2640 camera module', 'category' => 'boards'],
            ['name' => 'MicroSD Card', 'description' => '32GB or larger, Class 10', 'category' => 'storage'],
            ['name' => 'USB-to-Serial Adapter', 'description' => 'FTDI or CP2102 for programming', 'category' => 'boards'],
            ['name' => 'USB Cable', 'description' => 'Micro USB, long enough to reach car outlet', 'category' => 'wires'],
            ['name' => 'USB Car Charger', 'description' => '5V 2A minimum output', 'category' => 'power'],
            ['name' => 'Suction Cup Mount', 'description' => 'Small camera/phone mount for windshield', 'category' => 'enclosures'],
            ['name' => 'Jumper Wires', 'description' => 'Female-to-female for FTDI connection', 'category' => 'wires'],
            ['name' => '3D Printed Case', 'description' => 'Enclosure for ESP32-CAM', 'category' => 'enclosures', 'is_optional' => true],
        ]);

        // Build 2: Rear Dashcam
        $rearDashcam = Build::create([
            'user_id' => $user->id,
            'name' => 'Rear Dashcam',
            'slug' => 'rear-dashcam',
            'description' => 'Second ESP32-CAM dashcam for the rear window. Same setup as front, longer cable routing.',
            'status' => 'planning',
            'sort_order' => 2,
            'instructions' => "# Rear Dashcam Build\n\n## Overview\nIdentical to the front dashcam but mounted on the rear window. Requires longer cable routing through the car.\n\n## Key Differences from Front\n- **Longer USB cable** needed (route along ceiling trim to rear)\n- Mount on rear window glass, angled to see behind\n- Consider a **USB extension cable** if standard cable isn't long enough\n- May need a second USB port on the car charger (dual-port charger)\n\n## Wiring\nSame as Front Dashcam — see that build for details.\n\n## Cable Routing\n1. Run USB cable from rear window along the headliner\n2. Tuck into the trim on the driver or passenger side\n3. Route down the A-pillar or along the door sill\n4. Connect to USB car charger at the 12V outlet\n\n> **Tip:** Use cable clips with adhesive backing to keep the cable neat along the headliner.",
        ]);

        $this->addParts($rearDashcam, [
            ['name' => 'ESP32-CAM', 'description' => 'ESP32 with OV2640 camera module', 'category' => 'boards'],
            ['name' => 'MicroSD Card', 'description' => '32GB or larger, Class 10', 'category' => 'storage'],
            ['name' => 'USB-to-Serial Adapter', 'description' => 'FTDI or CP2102 for programming', 'category' => 'boards'],
            ['name' => 'USB Cable', 'description' => 'Extra long micro USB for rear routing', 'category' => 'wires'],
            ['name' => 'USB Extension Cable', 'description' => 'If standard cable is too short', 'category' => 'wires', 'is_optional' => true],
            ['name' => 'Dual USB Car Charger', 'description' => '5V 2A per port, powers both dashcams', 'category' => 'power'],
            ['name' => 'Suction Cup Mount', 'description' => 'Small mount for rear window', 'category' => 'enclosures'],
            ['name' => 'Jumper Wires', 'description' => 'Female-to-female for FTDI connection', 'category' => 'wires'],
            ['name' => 'Cable Clips', 'description' => 'Adhesive cable management clips', 'category' => 'misc', 'is_optional' => true],
        ]);

        // Build 3: Camera Cart / Rover
        $cart = Build::create([
            'user_id' => $user->id,
            'name' => 'Camera Cart',
            'slug' => 'camera-cart',
            'description' => 'A motorized rover with one or more cameras for remote viewing. Controlled via Wi-Fi from phone or computer.',
            'status' => 'planning',
            'sort_order' => 3,
            'instructions' => "# Camera Cart Build\n\n## Overview\nA small wheeled rover with camera(s) that you can drive remotely via Wi-Fi. View the camera feed and control movement from your phone or laptop browser.\n\n## Chassis Assembly\n1. Mount the 4 DC motors into the chassis frame\n2. Attach wheels to each motor shaft\n3. Mount the L298N motor driver on top of the chassis\n4. Mount the ESP32-CAM on the front of the chassis\n\n## Wiring — Motor Driver\n- **Motor A (left side)**:\n  - Left front motor → OUT1, OUT2\n  - Left rear motor → wired in parallel\n- **Motor B (right side)**:\n  - Right front motor → OUT3, OUT4\n  - Right rear motor → wired in parallel\n- **L298N power**:\n  - Battery pack **+** → L298N 12V input\n  - Battery pack **-** → L298N GND\n  - L298N 5V output → ESP32-CAM 5V\n  - L298N GND → ESP32-CAM GND\n\n## Wiring — Control Pins\n- ESP32 **GPIO 12** → L298N IN1\n- ESP32 **GPIO 13** → L298N IN2\n- ESP32 **GPIO 14** → L298N IN3\n- ESP32 **GPIO 15** → L298N IN4\n- ESP32 **GPIO 2** → L298N ENA (PWM speed control)\n- ESP32 **GPIO 4** → L298N ENB (PWM speed control)\n\n## Software\n- Web server on ESP32 serves control page\n- Camera stream + directional buttons (forward, back, left, right)\n- Access via `http://<ESP32-IP>` on same Wi-Fi network\n\n## Optional: Ultrasonic Sensor\nAdd HC-SR04 to front for obstacle detection:\n- **Trig** → GPIO 2\n- **Echo** → GPIO 15 (through voltage divider: 5V→3.3V)\n- Auto-stop when obstacle detected within 15cm",
        ]);

        $this->addParts($cart, [
            ['name' => 'ESP32-CAM', 'description' => 'Main controller + camera', 'category' => 'boards'],
            ['name' => 'L298N Motor Driver', 'description' => 'Dual H-bridge for 4 DC motors', 'category' => 'actuators'],
            ['name' => 'DC Motors', 'description' => '4x DC gear motors (3-6V)', 'category' => 'actuators', 'quantity_needed' => 4],
            ['name' => 'Wheels', 'description' => '4x wheels matching motor shafts', 'category' => 'misc', 'quantity_needed' => 4],
            ['name' => 'Robot Chassis', 'description' => '4WD chassis kit or custom platform', 'category' => 'enclosures'],
            ['name' => 'Battery Pack', 'description' => '4x AA or 2S LiPo for motors', 'category' => 'power'],
            ['name' => 'Jumper Wires', 'description' => 'Male-to-female for connections', 'category' => 'wires'],
            ['name' => 'MicroSD Card', 'description' => 'For recording if desired', 'category' => 'storage', 'is_optional' => true],
            ['name' => 'HC-SR04 Ultrasonic Sensor', 'description' => 'Obstacle detection (optional)', 'category' => 'sensors', 'is_optional' => true],
            ['name' => 'Second ESP32-CAM', 'description' => 'Rear-facing camera (optional)', 'category' => 'boards', 'is_optional' => true],
        ]);

        // Build 4: LED Necklace
        $necklace = Build::create([
            'user_id' => $user->id,
            'name' => 'LED Necklace',
            'slug' => 'led-necklace',
            'description' => 'A wearable LED necklace with addressable RGB LEDs. Multiple color modes and patterns, powered by a small LiPo battery.',
            'status' => 'planning',
            'sort_order' => 4,
            'instructions' => "# LED Necklace Build\n\n## Overview\nA wearable necklace with a strip of addressable NeoPixel LEDs, controlled by an Arduino Nano. Cycle through color modes with a button press. Powered by a small rechargeable battery.\n\n## Components Layout\n- Arduino Nano sits at the back of the neck (inside a small pouch or 3D printed case)\n- LED strip wraps around the front in a decorative pattern\n- Button on the side to change modes\n- Battery in the case with the Nano\n\n## Wiring\n1. **NeoPixel Data In** → Arduino Nano **D6** (with 470Ω resistor in series)\n2. **NeoPixel 5V** → Battery **+** (through switch if using one)\n3. **NeoPixel GND** → Arduino **GND** → Battery **-**\n4. **Button** → Arduino **D2** (with internal pull-up, other leg to GND)\n5. **Battery +** → Arduino **VIN** (if 7-12V) or **5V** (if regulated 5V)\n\n> **Important:** Add a 1000µF capacitor across the NeoPixel power lines to prevent voltage spikes.\n\n## LED Modes\n1. **Rainbow cycle** — smooth color rotation\n2. **Solid color** — warm white\n3. **Breathing** — slow pulse effect\n4. **Sparkle** — random twinkle\n5. **Off**\n\nButton press cycles through modes 1→2→3→4→5→1.\n\n## Assembly\n- Sew or glue LED strip to a fabric choker or chain\n- Use heat shrink tubing on solder joints for durability\n- Keep wires short and tidy\n- Test all connections before wearing!",
        ]);

        $this->addParts($necklace, [
            ['name' => 'Arduino Nano', 'description' => 'Small form factor controller', 'category' => 'boards'],
            ['name' => 'NeoPixel LED Strip', 'description' => 'WS2812B, 30 LEDs/m, ~20-30cm length', 'category' => 'displays'],
            ['name' => 'LiPo Battery', 'description' => '3.7V 500-1000mAh rechargeable', 'category' => 'power'],
            ['name' => 'Push Button', 'description' => 'Small tactile button for mode switching', 'category' => 'misc'],
            ['name' => '470Ω Resistor', 'description' => 'Data line protection for NeoPixels', 'category' => 'misc'],
            ['name' => '1000µF Capacitor', 'description' => 'Power smoothing for NeoPixels', 'category' => 'misc'],
            ['name' => 'Jumper Wires', 'description' => 'Short wires for connections', 'category' => 'wires'],
            ['name' => 'USB Cable', 'description' => 'Mini USB for programming the Nano', 'category' => 'wires'],
            ['name' => 'Chain or Choker Base', 'description' => 'Wearable base for mounting LEDs', 'category' => 'misc'],
            ['name' => 'Small Enclosure', 'description' => 'For Arduino + battery at the back', 'category' => 'enclosures', 'is_optional' => true],
        ]);
    }

    private function addParts(Build $build, array $parts): void
    {
        foreach ($parts as $i => $part) {
            BuildPart::create([
                'build_id' => $build->id,
                'name' => $part['name'],
                'description' => $part['description'] ?? null,
                'category' => $part['category'] ?? 'misc',
                'quantity_needed' => $part['quantity_needed'] ?? 1,
                'is_optional' => $part['is_optional'] ?? false,
                'sort_order' => $i,
            ]);
        }
    }
}
