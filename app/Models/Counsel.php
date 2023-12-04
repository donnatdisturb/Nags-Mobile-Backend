<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Counsel extends Model
{
    use HasFactory;

    public $table = 'counsil';
    protected $primaryKey = 'id';

    protected $fillable = ['scheduled_date', 'start_time','end_time','guidance_id','student_id','Status','createdby'];

    public function guidance()
    {
        return $this->belongsTo(Guidance::class,'guidance_id','id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'createdby','id');
    }


}
