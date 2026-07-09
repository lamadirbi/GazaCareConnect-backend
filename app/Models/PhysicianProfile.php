<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhysicianProfile extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'specialty',
        'certificate',
        'certificate_file_id',
        'certificate_file_ids',
        'verification_status',
        'verified_at',
        'verified_by',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'certificate_file_ids' => 'array',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function certificateFile(): BelongsTo
    {
        return $this->belongsTo(MedicalFile::class, 'certificate_file_id');
    }

    /** @return list<int> */
    public function orderedCertificateFileIds(): array
    {
        $raw = $this->certificate_file_ids;
        if (! is_array($raw)) {
            $raw = [];
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $raw))));
        if ($ids !== []) {
            return $ids;
        }
        if ($this->certificate_file_id) {
            return [(int) $this->certificate_file_id];
        }

        return [];
    }

    public function hydrateCertificateFilesRelation(): void
    {
        $ids = $this->orderedCertificateFileIds();
        if ($ids === []) {
            $this->setRelation('certificateFiles', collect());
            $this->setRelation('certificateFile', null);

            return;
        }

        $files = MedicalFile::query()
            ->whereIn('id', $ids)
            ->get(['id', 'original_name', 'mime_type', 'file_kind', 'size_bytes', 'created_at']);

        $ordered = collect($ids)
            ->map(fn (int $id) => $files->firstWhere('id', $id))
            ->filter()
            ->values();

        $this->setRelation('certificateFiles', $ordered);
        $first = $ordered->first();
        $this->setRelation('certificateFile', $first);
    }
}


