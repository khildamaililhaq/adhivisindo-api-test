<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lecturer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'photo',
    ];

    public function learningModules(): BelongsToMany
    {
        return $this->belongsToMany(LearningModule::class, 'lecturer_learning_module');
    }
}