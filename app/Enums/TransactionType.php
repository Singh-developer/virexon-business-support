<?php
namespace App\Enums;

enum TransactionType: string
{
    case PAYMENT = 'payment';
    case REFUND = 'refund';
    case ADJUSTMENT = 'adjustment';
    case FEE = 'fee';
    case CREDIT = 'credit';
    case DEBIT = 'debit';
}
