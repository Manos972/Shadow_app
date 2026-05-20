<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Import extends Model
{
    protected $fillable = [
        'user_id', 'team_id', 'filename', 'type', 'status',
        'total_rows', 'imported_rows', 'skipped_rows', 'duplicate_rows',
        'column_mapping', 'preview_data', 'error_message', 'file_path',
    ];

    protected function casts(): array
    {
        return [
            'column_mapping' => 'array',
            'preview_data' => 'array',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
