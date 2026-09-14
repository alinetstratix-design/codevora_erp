<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Design;

class VisualBuilderAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test 14: Client-Supplied Financial Data Spoofing
     */
    public function test_backend_ignores_spoofed_financial_data_in_preview()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Send a fake grand_total and subtotal to the preview API
        $payload = [
            'project_name' => 'Spoof Test',
            'client_name' => 'Hacker',
            'date' => date('Y-m-d'),
            'quote_no' => 'QT-9999',
            'subtotal' => 10.00, // SPOOFED
            'grand_total' => 10.00, // SPOOFED
            'items' => [
                [
                    'position' => 'Item 1',
                    'product_id' => 1,
                    'qty' => 1,
                    'dimension_w' => 1000,
                    'dimension_h' => 1000,
                    'sizes' => [
                        ['width' => 1000, 'height' => 1000, 'quantity' => 1, 'unit' => 'mm']
                    ],
                    'unit' => 'mm',
                    'line_total' => 5.00 // SPOOFED
                ]
            ]
        ];

        // Ensure we hit the new calculate endpoint
        $response = $this->postJson('/quotations/calculate', $payload);

        $response->assertStatus(200);
        $data = $response->json();
        
        $this->assertNotEquals(10.00, $data['quotation_data']['subtotal'] ?? 0);
        $this->assertNotEquals(10.00, $data['quotation_data']['grand_total'] ?? 0);
        if (isset($data['items'][0])) {
            $this->assertNotEquals(5.00, $data['items'][0]['line_total'] ?? 0);
        }
    }

    /**
     * Test 15: Drawing Image Security - Size limits and Malformed Base64
     */
    public function test_drawing_image_security_rejects_dangerous_payloads()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $payload = [
            'project_name' => 'Security Test',
            'client_name' => 'Hacker',
            'date' => date('Y-m-d'),
            'quote_no' => 'QT-9999',
            'items' => [
                [
                    'position' => 'Item 1',
                    'qty' => 1,
                    'dimension_w' => 1000,
                    'dimension_h' => 1000,
                    // Malformed base64 with PHP script
                    'drawing_b64' => 'data:image/php;base64,' . base64_encode('<?php phpinfo(); ?>'),
                ]
            ]
        ];

        $response = $this->post('/quotations', $payload);
        
        $files = \Illuminate\Support\Facades\Storage::disk('public')->files('drawings');
        $phpFiles = array_filter($files, fn($file) => str_ends_with($file, '.php'));
        $this->assertEmpty($phpFiles);
    }
}
