<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['loan_id', 'borrower_id', 'received_by', 'reference', 'amount', 'method', 'paid_at', 'status', 'metadata'])]
class Payment extends Model
{
    use HasFactory;

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(Borrower::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
