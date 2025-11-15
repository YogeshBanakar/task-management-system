<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\User;

class Task extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'created_by',
        'assigned_to',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'due_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeVisibleTo($query, User $user)
    {
        $hierarchyLevel = $user->getHierarchyLevel();

        if ($hierarchyLevel === 3) {
            return $query;
        } elseif ($hierarchyLevel === 2) {
            return $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                ->orWhere('assigned_to', $user->id)
                ->orWhereHas('assignee.roles', function ($roleQuery) {
                    $roleQuery->where('hierarchy_level', '<', 2); 
                })
                ->whereHas('creator.roles', function ($roleQuery) {
                    $roleQuery->where('hierarchy_level', '<', 3); 
                });
            });
        } else {
            return $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('assigned_to', $user->id);
            });
        }
    }
}
