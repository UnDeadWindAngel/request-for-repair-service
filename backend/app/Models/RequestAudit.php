<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestAudit extends Model
{
    use HasFactory;

    protected $table = 'request_audits';

    public $timestamps = false;

    protected $fillable = [
        'request_id',
        'user_id',
        'action',
        'old_status',
        'new_status',
        'old_assigned_to',
        'new_assigned_to',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(RepairRequest::class, 'request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
