<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donatur extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kontak',
        'jenis_donatur',
         'user_id'
    ];

    public function donasis()
    {
        return $this->hasMany(Donasi::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
