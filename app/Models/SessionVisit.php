<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SessionVisit extends Model
{
    use HasFactory;
    
    protected $table = 'session_visits';
    
    protected $fillable = [
        'session_id',
        'page_name',
        'page_url',
        'action_type',
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Get the session for this visit
     */
    public function session()
    {
        return $this->belongsTo(Session::class);
    }
    
    /**
     * Scope by action type
     */
    public function scopeByActionType($query, $actionType)
    {
        return $query->where('action_type', $actionType);
    }
    
    /**
     * Scope by page name
     */
    public function scopeByPage($query, $pageName)
    {
        return $query->where('page_name', $pageName);
    }
}
