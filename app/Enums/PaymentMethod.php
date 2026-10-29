<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Simulated in the demo: no payment gateway is called.
 */
enum PaymentMethod: string implements HasLabel
{
    case Qris = 'qris';
    case BankTransfer = 'bank_transfer';

    public function getLabel(): string
    {
        return match ($this) {
            self::Qris => 'QRIS',
            self::BankTransfer => __('Transfer bank'),
        };
    }
}
