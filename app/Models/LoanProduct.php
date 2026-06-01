<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'description', 'principal_type', 'fixed_principal', 'min_principal', 'max_principal', 'interest_method', 'interest_rate', 'interest_period', 'repayment_period', 'repayment_period_type', 'allows_early_settlement', 'early_settlement_rate', 'rollover_penalty_enabled', 'rollover_application', 'status'])]
class LoanProduct extends Model
{
    use HasFactory, SoftDeletes;

    public function attachmentRequirements(): HasMany
    {
        return $this->hasMany(ProductAttachmentRequirement::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(LoanApplication::class);
    }
}
