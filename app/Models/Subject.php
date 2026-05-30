<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = ['name', 'description', 'lab_count'];

    /**
     * Записи в очереди по этому предмету, упорядоченные по позиции.
     */
    public function queueEntries(): HasMany
    {
        return $this->hasMany(QueueEntry::class)->orderBy('position');
    }
}
