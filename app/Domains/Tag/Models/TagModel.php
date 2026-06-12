<?php

namespace App\Domains\Tag\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $name
 * @property string $color
 */
class TagModel extends Model
{
    use HasUlids;

    protected $table = 'tags';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['name', 'color'];
}
