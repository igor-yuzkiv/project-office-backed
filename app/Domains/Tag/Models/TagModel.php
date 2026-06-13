<?php

namespace App\Domains\Tag\Models;

use Database\Factories\TagModelFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $name
 * @property string $color
 */
class TagModel extends Model
{
    /** @use HasFactory<TagModelFactory> */
    use HasFactory, HasUlids;

    protected $table = 'tags';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['name', 'color'];

    public static function newFactory(): TagModelFactory
    {
        return TagModelFactory::new();
    }
}
