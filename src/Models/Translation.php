<?php

namespace Candeno\LaravelTranslations\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    public $timestamps = false;

    protected $table = 'translations';

    protected $fillable = [
        'table_name',
        'row_id',
        'locale',
        'field',
        'value',
    ];
}
