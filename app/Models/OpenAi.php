<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpenAi extends Model
{
    use HasFactory;

    public $fillable = ['response', 'tokens','model'];

    public function getResponseAttribute($value){
        return json_decode($value);
    }
}
