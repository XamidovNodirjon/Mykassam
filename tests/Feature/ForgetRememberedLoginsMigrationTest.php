<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

function runForgetRememberedLoginsMigration(): void
{
    (require database_path('migrations/2026_10_21_091818_forget_remembered_logins.php'))->up();
}

it('stops an old remember-me cookie from signing the user back in', function () {
    $owner = User::factory()->owner()->create(['remember_token' => 'old-remember-token']);
    $recaller = $owner->id.'|old-remember-token|'.$owner->password;

    runForgetRememberedLoginsMigration();

    $response = $this->withCookie(Auth::guard('web')->getRecallerName(), $recaller)
        ->get(route('owner.dashboard'));

    $response->assertRedirect(route('login'));
    $this->assertGuest();
    expect($owner->refresh()->remember_token)->toBeNull();
});
