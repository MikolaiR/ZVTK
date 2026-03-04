<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructionsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('features.client_blade_enabled', true);
    }

    public function test_guest_is_redirected_from_instructions_page(): void
    {
        $response = $this->get('/instructions');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_open_instructions_page(): void
    {
        /** @var User $user */
        $user = User::factory()->createOne();

        $response = $this->actingAs($user)->get('/instructions');

        $response->assertOk();
        $response->assertSee('Инструкция по работе с программой');
        $response->assertSee('Как добавить новый автомобиль');
    }
}
