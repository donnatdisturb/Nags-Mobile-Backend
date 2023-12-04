<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YearLevel extends Model
{
    use HasFactory;
    public $table = 'YearLevel';
    protected $primaryKey = 'id';

    protected $fillable = ['Name'];
}
