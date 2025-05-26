<?php

namespace App\Enums;

enum PaymentMethod : string
{
    case CREDIT_CARD = 'credit_card';

    case BANK_TRANSFER = 'bank_transfer';

    case E_WALLET = 'e_wallet';

    case CASH = 'cash';
}
