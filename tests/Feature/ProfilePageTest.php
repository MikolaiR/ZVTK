<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('features.client_blade_enabled', true);
    }

    public function test_guest_is_redirected_from_profile_page(): void
    {
        $response = $this->get('/profile');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_open_profile_blade_page(): void
    {
        /** @var User $user */
        $user = User::factory()->createOne([
            'name' => 'Client User',
            'email' => 'client@example.com',
        ]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
        $response->assertViewIs('client.profile');
        $response->assertSee('Client User');
        $response->assertSee('client@example.com');
    }
}
