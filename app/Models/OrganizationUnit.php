<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['organization_level_id', 'parent_id', 'name', 'code', 'latitude', 'longitude', 'service_radius_km', 'disbursement_limit', 'is_active'])]
class OrganizationUnit extends Model
{
    use HasFactory;

    public function level(): BelongsTo
    {
        return $this->belongsTo(OrganizationLevel::class, 'organization_level_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function borrowers(): HasMany
    {
        return $this->hasMany(Borrower::class);
    }
}
