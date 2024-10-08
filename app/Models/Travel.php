<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Travel extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function road(){
        return $this->hasMany(Road::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}
