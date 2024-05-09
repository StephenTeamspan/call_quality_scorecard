<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CQS extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_name',
        'LOB',
        'date_of_recording',
        'workorder',
        'type_of_call',
        'auditor',
        'audit_date',
        'time_processed',
        'date_processed',
        'CTQ',
        'call_summary',
        'call_recording',
        'strengths',
        'opportunities',
        'status',
        'comments',
        'score',
    ];

    protected $casts = [
        'CTQ' => 'array',
    ];

    
}
