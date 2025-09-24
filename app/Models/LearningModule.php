<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class LearningModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'photo',
    ];

    public function lecturers(): BelongsToMany
    {
        return $this->belongsToMany(Lecturer::class, 'lecturer_learning_module');
    }

    public function getPhotoAttribute($value)
    {
        return $value ? Storage::url($value) : null;
    }
}