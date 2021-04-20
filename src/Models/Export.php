<?php

namespace Thomascombe\BackpackAsyncExport\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Thomascombe\BackpackAsyncExport\Enums\ExportStatus;
use Thomascombe\BackpackAsyncExport\Exports\ExportWithName;

class Export extends Model
{
    use CrudTrait;
    use SoftDeletes;

    public const COLUMN_USER_ID = 'user_id';
    public const COLUMN_EXPORT_TYPE = 'export_type';
    public const COLUMN_FILENAME = 'filename';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_ERROR = 'error';
    public const COLUMN_COMPLETED_AT = 'completed_at';

    protected $fillable = [
        self::COLUMN_USER_ID,
        self::COLUMN_EXPORT_TYPE,
        self::COLUMN_FILENAME,
        self::COLUMN_STATUS,
        self::COLUMN_ERROR,
        self::COLUMN_COMPLETED_AT,
    ];

    protected $casts = [
        self::COLUMN_USER_ID => 'int',
    ];

    protected $dates = [
        self::COLUMN_COMPLETED_AT,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('backpack_async_export.user_model'));
    }

    public function getExportTypeNameAttribute(): string
    {
        $exportType = $this->{self::COLUMN_EXPORT_TYPE};
        if (is_subclass_of($exportType, ExportWithName::class)) {
            return $exportType::getName();
        }

        return $exportType;
    }

    public function getStoragePathAttribute(): string
    {
        return storage_path('app/' . $this->{self::COLUMN_FILENAME});
    }

    public function getDownloadButton(): string
    {
        if (ExportStatus::Successful === $this->{self::COLUMN_STATUS}) {
            $url = route(
                'export.download',
                [
                    'export' => $this->id,
                ]
            );

            return '<a href="' . $url . '" class="btn btn-xs btn-default"> <span class="fa fa-download"></span>'
                . ' Télécharger</a>';
        }

        return '<button type="button" class="btn btn-xs btn-default" disabled="disabled">'
            . '<span class="fa fa-download"></span> Télécharger</button>';
    }
}