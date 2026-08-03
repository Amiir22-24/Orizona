<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Territory extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'name',
        'region',
        'property_count',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
