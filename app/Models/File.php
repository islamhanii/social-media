<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = ["id"];

    /*-----------------------------------------------------------------------------------------------*/

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function getLinkAttribute($value)
    {
        if ($value) {
            return env("APP_URL") . "/uploads/" . $value;
        }
    }
}
