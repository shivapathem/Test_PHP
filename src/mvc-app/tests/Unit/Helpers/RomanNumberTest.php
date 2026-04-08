<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;
use App\Helpers\RomanNumber;
use PHPUnit\Framework\Attributes\Test;

class RomanNumberTest extends TestCase
{
    #[Test]
    public function it_converts_1_to_I()
    {
        $this->assertSame('I', RomanNumber::numberToRoman(1));
    }

    #[Test]
    public function it_converts_4_to_IV()
    {
        $this->assertSame('IV', RomanNumber::numberToRoman(4));
    }

    #[Test]
    public function it_converts_5_to_V()
    {
        $this->assertSame('V', RomanNumber::numberToRoman(5));
    }

    #[Test]
    public function it_converts_9_to_IX()
    {
        $this->assertSame('IX', RomanNumber::numberToRoman(9));
    }

    #[Test]
    public function it_converts_10_to_X()
    {
        $this->assertSame('X', RomanNumber::numberToRoman(10));
    }

    #[Test]
    public function it_converts_40_to_XL()
    {
        $this->assertSame('XL', RomanNumber::numberToRoman(40));
    }

    #[Test]
    public function it_converts_50_to_L()
    {
        $this->assertSame('L', RomanNumber::numberToRoman(50));
    }

    #[Test]
    public function it_converts_90_to_XC()
    {
        $this->assertSame('XC', RomanNumber::numberToRoman(90));
    }

    #[Test]
    public function it_converts_100_to_C()
    {
        $this->assertSame('C', RomanNumber::numberToRoman(100));
    }

    #[Test]
    public function it_converts_400_to_CD()
    {
        $this->assertSame('CD', RomanNumber::numberToRoman(400));
    }

    #[Test]
    public function it_converts_500_to_D()
    {
        $this->assertSame('D', RomanNumber::numberToRoman(500));
    }

    #[Test]
    public function it_converts_900_to_CM()
    {
        $this->assertSame('CM', RomanNumber::numberToRoman(900));
    }

    #[Test]
    public function it_converts_1000_to_M()
    {
        $this->assertSame('M', RomanNumber::numberToRoman(1000));
    }

    #[Test]
    public function it_converts_1990_to_MCMXC()
    {
        $this->assertSame('MCMXC', RomanNumber::numberToRoman(1990));
    }

    #[Test]
    public function it_converts_2023_to_MMXXIII()
    {
        $this->assertSame('MMXXIII', RomanNumber::numberToRoman(2023));
    }

    #[Test]
    public function it_converts_3999_to_MMMCMXCIX()
    {
        $this->assertSame('MMMCMXCIX', RomanNumber::numberToRoman(3999));
    }

    #[Test]
    public function it_converts_0_to_empty_string()
    {
        $this->assertSame('', RomanNumber::numberToRoman(0));
    }

    #[Test]
    public function it_returns_empty_for_negative_numbers()
    {
        $this->assertSame('', RomanNumber::numberToRoman(-5));
    }

    #[Test]
    public function it_handles_large_numbers_above_3999()
    {
        $this->assertSame('MMMM', RomanNumber::numberToRoman(4000));
    }
}
