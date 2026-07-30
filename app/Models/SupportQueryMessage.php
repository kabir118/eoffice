<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportQueryMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'support_query_id',
        'user_id',
        'message',
    ];

    public function supportQuery()
    {
        return $this->belongsTo(SupportQuery::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
