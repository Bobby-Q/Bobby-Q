<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardTest extends TestCase
{
    public function test_dashboard_loads_with_security_headers(): void
    {
        $this->withoutVite();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Loan Suite');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }
}
