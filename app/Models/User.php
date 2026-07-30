<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department_id',
        'designation_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if the user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if the user is authorized to view all university files (VC, Registrar, Super Admin).
     */
    public function canViewAllFiles(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $email = strtolower($this->email);
        return str_contains($email, 'registrar') || $email === 'registrar@ustb.edu.pk';
    }

    /**
     * Check if the user is the Registrar.
     */
    public function isRegistrar(): bool
    {
        $email = strtolower($this->email);
        $dCode = strtolower($this->department->code ?? '');
        $dName = strtolower($this->department->name ?? '');
        $desig = strtolower($this->designation->title ?? '');

        return str_contains($email, 'registrar') || $dCode === 'reg' || str_contains($dName, 'registrar') || str_contains($desig, 'registrar');
    }

    /**
     * Check if the user is authorized to compose/broadcast e-Notifications (Registrar Office & Super Admin).
     */
    public function canComposeNotification(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $email = strtolower($this->email);
        $dCode = strtolower($this->department->code ?? '');
        $dName = strtolower($this->department->name ?? '');
        $desig = strtolower($this->designation->title ?? '');

        return $dCode === 'reg' || 
               str_contains($dName, 'registrar') || 
               str_contains($desig, 'registrar') || 
               str_contains($desig, 'assistant registrar') || 
               str_contains($desig, 'deputy registrar') || 
               str_contains($email, 'registrar');
    }

    /**
     * Check if the user is the Controller of Examinations.
     */
    public function isControllerOfExaminations(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $email = strtolower($this->email);
        $name = strtolower($this->name);
        $dCode = strtolower($this->department->code ?? '');
        $dName = strtolower($this->department->name ?? '');
        $desig = strtolower($this->designation->title ?? '');

        return str_contains($email, 'exam') || str_contains($email, 'controller') || str_contains($name, 'controller') || str_contains($name, 'examination') || $dCode === 'exam' || $dCode === 'coe' || str_contains($dName, 'exam') || str_contains($dName, 'controller') || str_contains($desig, 'controller') || str_contains($desig, 'exam');
    }

    /**
     * Get user's department.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get user's designation.
     */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    /**
     * Documents created by the user.
     */
    public function createdDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'creator_id');
    }

    /**
     * Documents currently held by the user.
     */
    public function heldDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'current_holder_id');
    }

    /**
     * Comments written by the user.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * e-Notifications assigned to this user.
     */
    public function notifications(): BelongsToMany
    {
        return $this->belongsToMany(Notification::class, 'notification_user')
                    ->withPivot('read_at');
    }

    /**
     * Unread e-Notifications assigned to this user.
     */
    public function unreadNotifications(): BelongsToMany
    {
        return $this->belongsToMany(Notification::class, 'notification_user')
                    ->wherePivotNull('read_at')
                    ->withPivot('read_at');
    }
}
