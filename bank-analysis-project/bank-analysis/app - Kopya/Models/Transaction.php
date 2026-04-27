<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Transaction extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'category', 'transaction_date',
        'description', 'location', 'transaction_type',
        'balance_after', 'source', 'external_id',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    // Gelir mi?
    public function isIncome(): bool
    {
        return $this->amount > 0;
    }

    // Gider mi?
    public function isExpense(): bool
    {
        return $this->amount < 0;
    }

    // Tutarın görsel değeri
    public function getDisplayAmountAttribute(): string
    {
        return number_format(abs($this->amount), 2) . ' ₹';
    }

    // Scopes
    public function scopeExpenses(Builder $query): Builder
    {
        return $query->where('amount', '<', 0);
    }

    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('amount', '>', 0);
    }

    public function scopeInPeriod(Builder $query, string $start, string $end): Builder
    {
        return $query->whereBetween('transaction_date', [$start, $end]);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }
}
