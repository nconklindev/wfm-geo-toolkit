<?php

namespace App\Support;

use Spatie\Csp\Nonce\NonceGenerator;
use Vite;

class ViteNonceGenerator implements NonceGenerator
{
    public function generate(): string
    {
        return Vite::cspNonce();
    }
}
