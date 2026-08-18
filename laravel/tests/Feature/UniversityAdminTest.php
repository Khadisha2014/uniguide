<?php

namespace Tests\Feature;

use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversityAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_panel(): void
    {
        $this->get('/admin/universities')->assertRedirect('/admin/login');
    }

    public function test_admin_can_create_university(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post('/admin/universities', [
            'name' => 'Test University', 'short_name' => 'TU', 'city' => 'Астана',
            'country' => 'Казахстан', 'flag' => '🇰🇿', 'world_rank' => 100,
            'acceptance_rate' => 45, 'international_rate' => 20,
            'tuition' => '$10 тыс.', 'tuition_value' => 10,
            'requirements_text' => "IELTS 6.5\nGPA 3.5+", 'deadline' => '1 марта',
            'type' => 'Государственный', 'accent' => '#0d6b52', 'is_published' => 1,
        ]);

        $response->assertRedirect(route('admin.universities.index'));
        $this->assertDatabaseHas(University::class, ['name' => 'Test University']);
    }
}
