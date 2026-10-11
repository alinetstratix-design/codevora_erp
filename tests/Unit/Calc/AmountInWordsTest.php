<?php

namespace Tests\Unit\Calc;

use App\Services\QuotationCalculationService;
use PHPUnit\Framework\TestCase;

class AmountInWordsTest extends TestCase
{
    private function words(float $amount): string
    {
        // The method does not use instance state; construct with lightweight mocks.
        $service = (new \ReflectionClass(QuotationCalculationService::class))->newInstanceWithoutConstructor();
        return $service->amountInWords($amount);
    }

    public function test_zero_and_singular_rupee(): void
    {
        $this->assertSame('Zero Rupees Only', $this->words(0));
        $this->assertSame('One Rupee Only', $this->words(1));
    }

    public function test_indian_grouping_without_plurals_or_double_spaces(): void
    {
        $this->assertSame('One Hundred Rupees Only', $this->words(100));
        $this->assertSame('One Thousand Rupees Only', $this->words(1000));
        $this->assertSame('One Lakh Rupees Only', $this->words(100000));
        $this->assertSame('One Crore Rupees Only', $this->words(10000000));
        $this->assertSame(
            'One Lakh Twenty Three Thousand Four Hundred and Fifty Six Rupees Only',
            $this->words(123456)
        );
        $this->assertSame(
            'Twelve Lakh Thirty Four Thousand Five Hundred and Sixty Seven Rupees Only',
            $this->words(1234567)
        );
    }

    public function test_paise_are_spelled_out(): void
    {
        $this->assertSame(
            'One Crore Twenty Three Lakh Forty Five Thousand Six Hundred and Seventy Eight Rupees and Fifty Paise Only',
            $this->words(12345678.5)
        );
        $this->assertSame('Nine Hundred and Ninety Nine Rupees and Ninety Nine Paise Only', $this->words(999.99));
    }

    public function test_no_double_spaces_anywhere(): void
    {
        foreach ([0, 1, 21, 105, 2500.5, 12345678.5, 999.99] as $amount) {
            $this->assertStringNotContainsString('  ', $this->words((float) $amount));
        }
    }
}
