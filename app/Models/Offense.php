<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offense extends Model
{
    use HasFactory;
    public $table = 'offense';
    protected $primaryKey = 'id';

    protected $fillable = ['offensename'];
}
