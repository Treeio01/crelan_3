<?php

namespace Tests\Feature;

use Tests\TestCase;

class VisitEndpointTest extends TestCase
{
    public function test_visit_endpoint_accepts_valid_payload_and_returns_success(): void
    {
        $response = $this->postJson('/api/visit', [
            'event' => 'visit',
            'locale' => 'nl',
        ]);

        $response->assertOk()->assertJson([
            'success' => true,
        ]);
    }

    public function test_visit_endpoint_rejects_invalid_event(): void
    {
        $response = $this->postJson('/api/visit', [
            'event' => 'unexpected-event',
        ]);

        $response->assertStatus(422);
    }
}
