<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServerGroup extends Model
{
     use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function servers()
    {
        return $this->hasMany(Server::class, 'group_id');
    }
}
