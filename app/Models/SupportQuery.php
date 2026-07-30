<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportQuery extends Model
{
    protected $fillable = ['user_id', 'subject', 'message', 'status', 'admin_notes'];

    /**
     * User who created the support query.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Conversation messages/replies in this ticket.
     */
    public function messages()
    {
        return $this->hasMany(SupportQueryMessage::class)->oldest();
    }
}
