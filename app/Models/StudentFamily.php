<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFamily extends Model
{
    use HasFactory;
    public $table = 'studentfamilies';
    protected $primaryKey = 'id';

    protected $fillable = ['fname', 'lname','phone','address','email','user_id'];
   
    public function students() 
    {
        return $this->HasMany(Student::class,'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
