<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_path',
        'status',
        'extracted_text',
        'result_json',
        'error_message'
    ];

    protected $casts = [
        'result_json' => 'array',
    ];
}
