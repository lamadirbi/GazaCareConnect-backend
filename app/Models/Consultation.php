<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    public const MODE_QUEUE = 'queue';
    public const MODE_DIRECT = 'direct';

    public const SEVERITY_MILD = 'mild';
    public const SEVERITY_MODERATE = 'moderate';
    public const SEVERITY_CRITICAL = 'critical';

    protected $fillable = [
        'patient_id',
        'physician_id',
        'assignment_mode',
        'question_text',
        'status',
        'submitted_at',
        'responded_at',
        'physician_response',
        'case_severity',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function physician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'physician_id');
    }

    public function medicalFiles(): HasMany
    {
        return $this->hasMany(MedicalFile::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ConsultationMessage::class)->orderBy('created_at');
    }
}
