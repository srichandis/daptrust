<?php

namespace Tests\Feature;

use App\Models\AdminCredential;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_valid_credentials_redirects_to_dashboard(): void
    {
        $response = $this->post(route('admin.login.submit'), [
            'username' => config('admin.username'),
            'password' => config('admin.password'),
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAsAdmin();
    }

    public function test_login_with_invalid_credentials_fails(): void
    {
        $this->post(route('admin.login.submit'), [
            'username' => 'admin',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('username');

        $this->assertNotAuthenticatedAsAdmin();
    }

    public function test_login_with_remember_sets_persistent_cookie(): void
    {
        $response = $this->post(route('admin.login.submit'), [
            'username' => config('admin.username'),
            'password' => config('admin.password'),
            'remember' => '1',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertCookie('admin_remember');
        $this->assertNotNull(AdminCredential::first()->remember_token);
    }

    public function test_remember_cookie_authenticates_without_session(): void
    {
        $token = Str::random(60);
        AdminCredential::ensureSeeded()->update(['remember_token' => Hash::make($token)]);

        // withUnencryptedCookie sends the raw token, matching what the
        // browser would send back for this (non-encrypted) remember cookie.
        $this->withUnencryptedCookie('admin_remember', $token)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_logout_clears_remember_token(): void
    {
        $token = Str::random(60);
        AdminCredential::ensureSeeded()->update(['remember_token' => Hash::make($token)]);

        $this->session(['admin_authenticated' => true]);

        $this->post(route('admin.logout'))->assertRedirect(route('admin.login'));
        $this->assertNull(AdminCredential::first()->remember_token);
    }

    public function test_forgot_password_with_wrong_answer_fails(): void
    {
        AdminCredential::ensureSeeded()->update([
            'security_question' => 'Favourite colour?',
            'security_answer' => Hash::make('blue'),
        ]);

        $this->post(route('admin.forgot.submit'), [
            'username' => config('admin.username'),
            'security_answer' => 'red',
        ])->assertSessionHasErrors('username');

        $this->assertNotAuthenticatedAsAdmin();
    }

    public function test_forgot_password_flow_resets_password(): void
    {
        AdminCredential::ensureSeeded()->update([
            'security_question' => 'Favourite colour?',
            'security_answer' => Hash::make('blue'),
        ]);

        $this->get(route('admin.forgot'))
            ->assertOk()
            ->assertSee('Favourite colour?');

        $this->post(route('admin.forgot.submit'), [
            'username' => config('admin.username'),
            'security_answer' => 'blue',
        ])->assertRedirect(route('admin.reset'));

        $this->get(route('admin.reset'))->assertOk()->assertSee('New password');

        $this->post(route('admin.reset.submit'), [
            'password' => 'brand-new-pass',
            'password_confirmation' => 'brand-new-pass',
        ])->assertRedirect(route('admin.login'));

        // Old password no longer works, new one does.
        $this->post(route('admin.login.submit'), [
            'username' => config('admin.username'),
            'password' => config('admin.password'),
        ])->assertSessionHasErrors('username');

        $this->post(route('admin.login.submit'), [
            'username' => config('admin.username'),
            'password' => 'brand-new-pass',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_reset_page_is_guarded_without_verification(): void
    {
        $this->get(route('admin.reset'))->assertRedirect(route('admin.forgot'));
    }

    private function assertAuthenticatedAsAdmin(): void
    {
        $this->assertTrue(session('admin_authenticated') === true);
    }

    private function assertNotAuthenticatedAsAdmin(): void
    {
        $this->assertFalse(session('admin_authenticated') === true);
    }
}
