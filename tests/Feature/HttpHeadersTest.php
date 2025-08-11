<?php

use function Pest\Laravel\get;

it('has proper security headers present', function () {
    get('/')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('X-XSS-Protection', '1; mode=block')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()')
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000')
        ->assertHeaderMissing('Phpdebugbar-Id');
});

it('has CSP headers', function () {
    get('/')
        ->assertHeader('Content-Security-Policy')
        ->assertHeaderMissing('X-Debug-Token');
});
