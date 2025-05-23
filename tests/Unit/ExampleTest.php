<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Illuminate\Support\Str;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }
}

test('contoh test unit sederhana', function () {
    expect(true)->toBeTrue();
});

test('fungsi helper string bekerja dengan baik', function () {
    // Pengujian fungsi string helper Laravel
    $result = Str::slug('Contoh String dengan Spasi');
    
    expect($result)->toBe('contoh-string-dengan-spasi');
});
