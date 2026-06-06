<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test public rendering of a static page.
     */
    public function test_can_view_public_static_page()
    {
        $page = Page::create([
            'slug' => 'test-terms',
            'title' => 'Test Terms of Service',
            'content' => '<p>These are test terms.</p>',
        ]);

        $response = $this->get('/p/test-terms');

        $response->assertStatus(200);
        $response->assertSee('Test Terms of Service');
    }

    /**
     * Test page not found returns 404.
     */
    public function test_missing_page_returns_404()
    {
        $response = $this->get('/p/non-existent-slug');
        $response->assertStatus(404);
    }

    /**
     * Test guest is redirected from admin pages management.
     */
    public function test_guest_cannot_access_admin_pages()
    {
        $response = $this->get('/admin/pages');
        $response->assertRedirect('/login');
    }

    /**
     * Test non-admin user cannot access admin pages.
     */
    public function test_non_admin_cannot_access_admin_pages()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get('/admin/pages');
        $response->assertRedirect('/dashboard');
    }

    /**
     * Test admin can view and update pages.
     */
    public function test_admin_can_view_and_update_pages()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        
        $page = Page::create([
            'slug' => 'privacy',
            'title' => 'Privacy Policy',
            'content' => 'Old content',
        ]);

        $response = $this->actingAs($admin)->get('/admin/pages');
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get("/admin/pages/{$page->id}/edit");
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->put("/admin/pages/{$page->id}", [
            'title' => 'Updated Privacy Policy',
            'content' => 'New updated content',
        ]);

        $response->assertRedirect('/admin/pages');
        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'title' => 'Updated Privacy Policy',
            'content' => 'New updated content',
        ]);
    }
}
