<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class File extends Model
{
    use SoftDeletes;

    public const PUBLIC_DISK = 'local';

    protected $fillable = [
        'owner_type',
        'owner_id',
        'created_by_user_id',
        'file_type',
        'drive',
        'url',
        'path',
        'original',
        'mime',
        'size',
        'sort_order',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::deleted(function ($model) {
            if (App::isProduction() && !$model->trashed()) {
                Storage::disk($model->drive)->delete($model->path);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public static function upload(UploadedFile $file, string $path = 'uploads', ?string $disk = null): array
    {
        $disk = $disk === null ? config('filesystems.cloud') : $disk;
        $folder = preg_replace('/\/$/', '', config('filesystems.disks.' . $disk . '.folder'));
        $file_path = Storage::disk($disk)->put($folder . '/' . $path, $file);

        return [
            'drive' => $disk,
            'url' => Storage::disk($disk)->url($file_path),
            'path' => $file_path,
            'original' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
        ];
    }

    public static function getOriginalsByOwner(string $owner_type, int $owner_id, ?array $file_types = null): array
    {
        $query = self::query()->select('original')
            ->where('owner_type', $owner_type)
            ->where('owner_id', $owner_id);

        if ($file_types && count($file_types)) {
            $query->whereIn('file_type', $file_types);
        }

        return $query->pluck('original')
            ->toArray();
    }

    public static function download(string $path): StreamedResponse
    {
        return Storage::download($path);
    }

    public static function read(string $path)
    {
        return Storage::get($path);
    }
}
