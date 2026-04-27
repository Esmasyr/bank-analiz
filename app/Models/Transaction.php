<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'reference_number',
        'customer_id',
        'amount',
        'account_balance_snapshot',
        'processed_at',
        'transaction_time',
        'type',
        'category',
        'status',
        'description',
    ];

    protected $attributes = [
        'status'   => 'completed',
        'type'     => 'debit',
    ];

    protected function casts(): array
    {
        return [
            'processed_at'          => 'date',
            'amount'                    => 'decimal:2',
            'account_balance_snapshot'  => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}