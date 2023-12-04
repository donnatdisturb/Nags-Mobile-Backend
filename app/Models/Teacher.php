<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;
    public $table = 'teachers';
    protected $primaryKey = 'id';

    protected $fillable = ['fname', 'lname','user_id','otp'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function students() 
    {
        return $this->HasMany(Student::class,'id');
    }
}
