<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Punishments extends Model
{
    use HasFactory;
    public $table = 'punishments';
    protected $primaryKey = 'id';

    protected $fillable = ['name'];

    public function student() 
    {
        return $this->belongsTo(Student::class,'student_id');
    }
    public function studentrecord() 
    {
        return $this->HasMany(StudentRecords::class);
    }
  
}
