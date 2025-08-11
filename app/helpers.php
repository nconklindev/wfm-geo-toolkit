<?php

use App\Support\ViteNonceGenerator;

if (! function_exists('csp_nonce')) {
    function csp_nonce(): string
    {
        return app(ViteNonceGenerator::class)->generate();
    }
}
