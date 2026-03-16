<?php

namespace Tests\Unit;

use App\Support\CssSanitizer;
use PHPUnit\Framework\TestCase;

class CssSanitizerTest extends TestCase
{
    /** @test */
    public function it_replaces_oklab_colors_with_transparent()
    {
        $in = 'color: oklab(0.7 0.2 -0.1); background: linear-gradient(45deg, oklab(0.5 0 0), #fff)';
        $out = CssSanitizer::sanitizeCssValue($in);
        $this->assertStringNotContainsString('oklab(', $out);
        $this->assertStringContainsString('transparent', $out);
    }

    /** @test */
    public function it_replaces_oklch_colors_with_transparent()
    {
        $in = 'border-color: oklch(59.69% 0.131 70.00); box-shadow: 0 0 0 2px oklch(70% 0.2 120)';
        $out = CssSanitizer::sanitizeCssValue($in);
        $this->assertStringNotContainsString('oklch(', $out);
        $this->assertStringContainsString('transparent', $out);
    }
}
