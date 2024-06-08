<?php

namespace App\Models\FileManager;

use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class FileManager extends MasterModel implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use UuidForKey;

    protected $appends = [
        'files',
        'category',
        'extension',
    ];

    public function getExtensionAttribute()
    {
        $file = FileManagerFile::where('file_manager_id', $this->getAttribute('id'))->first();
        if (! empty($file)) {
            return $file->extension;
        } else {
            return 'file';
        }
    }

    public function getFilesAttribute()
    {
        return FileManagerFile::where('file_manager_id', $this->getAttribute('id'))->get();
    }

    public function getCategoryAttribute()
    {
        return FileManagerCategoryPlain::where('id', $this->getAttribute('file_manager_category_id'))->first();
    }
}
