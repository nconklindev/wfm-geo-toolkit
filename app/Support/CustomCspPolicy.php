<?php

namespace App\Support;

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;
use Spatie\Csp\Scheme;

class CustomCspPolicy implements Preset
{
    public function configure(Policy $policy): void
    {
        $policy
            ->add(Directive::SCRIPT, [Keyword::SELF, Keyword::UNSAFE_EVAL, app()->environment('local') ? 'localhost:*' : null])
            ->add(Directive::STYLE, [Keyword::SELF, app()->environment('local') ? 'localhost:*' : null])
            ->add(Directive::FONT, [Keyword::SELF, Scheme::DATA, Scheme::BLOB, app()->environment('local') ? 'localhost:*' : null,
            ])
            ->add(Directive::OBJECT, [Keyword::NONE])
            ->add(Directive::CONNECT, [Scheme::WS, Scheme::WSS, Keyword::SELF])
            ->add(Directive::BASE, [Keyword::SELF])
            ->add(Directive::FORM_ACTION, [Keyword::SELF])
            ->add(Directive::IMG, [Keyword::SELF, Scheme::DATA, Keyword::UNSAFE_INLINE, app()->environment('local') ? 'localhost:*' : null,
                'blob:',
                '*.tiles.mapbox.com',
                '*.openstreetmap.org',
                'mt0.google.com',
                'mt1.google.com',
                'mt2.google.com',
                'mt3.google.com', // For Google Maps tiles
            ])
            ->addNonce(Directive::SCRIPT)
            ->addNonce(Directive::STYLE);
    }
}
