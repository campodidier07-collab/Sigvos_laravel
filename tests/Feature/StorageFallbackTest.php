<?php

namespace Tests\Feature;

use Tests\TestCase;

class StorageFallbackTest extends TestCase
{
    public function test_existing_storage_file_is_served()
    {
        $response = $this->get('/storage/lotes/3aWARoTJ8HwqZ6OY4Zc1W6mFT9I8s9BiewAi9e76.png');

        $response->assertStatus(200);
        $this->assertStringContainsString('image/', $response->headers->get('Content-Type'));
    }

    public function test_missing_storage_file_returns_fallback_svg()
    {
        $response = $this->get('/storage/cultivos/non_existent_image_123.jpg');

        $response->assertStatus(200);
        $this->assertStringContainsString('image/svg+xml', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Cultivo SIGVOS', $response->getContent());
    }

    public function test_missing_lote_file_returns_lote_svg()
    {
        $response = $this->get('/storage/lotes/missing_plot.png');

        $response->assertStatus(200);
        $this->assertStringContainsString('image/svg+xml', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Lote SIGVOS', $response->getContent());
    }
}
