<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = [
        'nama_file',
        'path',
        'tipe'
    ];
}
