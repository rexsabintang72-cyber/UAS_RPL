<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donasi extends Model
{
    use HasFactory;

    // WAJIB ADA → supaya Donasi::create() bisa jalan
    protected $fillable = [
        'donatur_id',
        'tanggal',
        'jenis_donasi',
        'jumlah',
        'status',
    ];

    public function donatur()
    {
        return $this->belongsTo(Donatur::class);
    }
}

