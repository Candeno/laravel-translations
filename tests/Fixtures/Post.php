<?php

namespace Candeno\LaravelTranslations\Tests\Fixtures;

use Candeno\LaravelTranslations\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasTranslations;

    public $timestamps = false;

    protected $guarded = [];

    protected array $translatable = ['title', 'subtitle'];
}
