<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairRequest extends Model
{
    use HasFactory;

    protected $table = 'requests';

    protected $fillable = [
        'clientName',
        'phone',
        'address',
        'problemText',
        'status',
        'assignedTo',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function assignedMaster()
    {
        return $this->belongsTo(User::class, 'assignedTo');
    }

    public function audits()
    {
        return $this->hasMany(RequestAudit::class, 'request_id');
    }
}
