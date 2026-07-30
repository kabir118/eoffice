<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'file_number',
        'title',
        'description',
        'priority',
        'status',
        'creator_id',
        'current_holder_id',
        'current_department_id',
        'attachment_path',
        'urgency_remarks',
        'is_available_to_registrar'
    ];

    protected $casts = [
        'is_available_to_registrar' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($document) {
            // Generate a unique tracking number: USTB-DEP-YYYY-XXXX
            $year = date('Y');
            $dept = Department::find($document->current_department_id);
            $deptCode = $dept ? strtoupper($dept->code) : 'GEN';
            
            $lastDoc = self::whereYear('created_at', $year)->latest('id')->first();
            $nextNumber = 1;
            if ($lastDoc) {
                // Extract last number
                $parts = explode('-', $lastDoc->file_number);
                $lastNum = end($parts);
                if (is_numeric($lastNum)) {
                    $nextNumber = (int)$lastNum + 1;
                }
            }

            $document->file_number = sprintf('USTB-%s-%s-%04d', $deptCode, $year, $nextNumber);
        });
    }

    /**
     * Creator of the document.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Current holder of the document.
     */
    public function currentHolder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_holder_id');
    }

    /**
     * Current department of the document.
     */
    public function currentDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'current_department_id');
    }

    /**
     * Tracking logs of the document.
     */
    public function tracks(): HasMany
    {
        return $this->hasMany(DocumentTrack::class, 'document_id')->orderBy('created_at', 'desc');
    }

    /**
     * Comments for the document.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'document_id')->orderBy('created_at', 'desc');
    }
}
