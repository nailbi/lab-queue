<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lab extends Model
{
    protected $fillable = ['subject_id', 'number', 'title'];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
