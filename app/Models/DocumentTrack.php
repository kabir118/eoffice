<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTrack extends Model
{
    protected $fillable = [
        'document_id',
        'sender_id',
        'receiver_id',
        'remarks',
        'status_at_transfer',
        'received_at',
        'attachment_path'
    ];

    protected $casts = [
        'received_at' => 'datetime'
    ];

    /**
     * Document being tracked.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    /**
     * Sender of the document.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Receiver of the document.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
