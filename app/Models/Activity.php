<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getImageAttribute($value){
        return 'http://127.0.0.1/storage/'.$value;
    }
}
