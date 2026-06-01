<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['borrower_id', 'type', 'disk', 'path', 'original_name', 'mime_type', 'size_bytes', 'verification_status', 'verified_by', 'verified_at'])]
class BorrowerDocument extends Model
{
    use HasFactory;

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(Borrower::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
