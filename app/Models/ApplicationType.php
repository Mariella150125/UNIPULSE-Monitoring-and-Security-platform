<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\AuditsActivity;

class ApplicationType extends Model
{
    use AuditsActivity;
    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Applications appartenant à ce type.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}