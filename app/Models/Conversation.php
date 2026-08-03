<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'property_id',
        'client_id',
        'agent_id',
        'admin_id',
        'status',
        'type',
        'is_archived',
        'last_message',
        'last_message_at',
        'closed_at',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
    ];

    public function participants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'conversation_participants');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    public function getLastMessageAttribute()
    {
        return $this->messages()->latest()->first();
    }
    
    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latest();
    }
}
