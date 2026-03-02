<?php

namespace Tests\Feature;

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InstructionsPageTest extends TestCase
{
    public function test_guest_is_redirected_from_instructions_page(): void
    {
        $response = $this->get('/instructions');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_open_instructions_page(): void
    {
        /** @var User $user */
        $user = User::factory()->makeOne();

        $response = $this->actingAs($user)->get('/instructions');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Instructions/Index'));
    }
}
