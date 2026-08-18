<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $this->get(route('admin.login'))->assertOk()->assertSee('Sign in');
    }

    public function test_admin_areas_redirect_guests_to_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.entries.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.accounts.index'))->assertRedirect(route('admin.login'));
    }

    public function test_valid_credentials_log_in(): void
    {
        $this->post(route('admin.login.submit'), [
            'username' => config('admin.username'),
            'password' => config('admin.password'),
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertTrue(session('admin_authenticated'));
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->post(route('admin.login.submit'), [
            'username' => 'admin',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('username');

        $this->assertNull(session('admin_authenticated'));
    }

    public function test_logout_clears_session(): void
    {
        $this->actingAsAdmin();

        $this->post(route('admin.logout'))->assertRedirect(route('admin.login'));
        $this->assertNull(session('admin_authenticated'));
    }

    protected function actingAsAdmin(): void
    {
        session(['admin_authenticated' => true]);
    }
}
