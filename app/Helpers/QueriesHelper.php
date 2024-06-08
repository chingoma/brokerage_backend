<?php

namespace App\Helpers;

class QueriesHelper
{
    public static function transactionsColumns(): array
    {
        return [
            'transactions.id',
            'transactions.title',
            'transactions.amount',
            'transactions.transaction_date',
            'transactions.debit',
            'transactions.credit',
            'transactions.reference',
            'transactions.category',
            'transactions.action',
            'transactions.description',
            'transactions.status',
            'transactions.uid',
            'transactions.client_id',
        ];
    }
}
