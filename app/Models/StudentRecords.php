<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentRecords extends Model
{
    use HasFactory;
    public $table = 'studentrecords';
    protected $primaryKey = 'id';

    protected $fillable = ['YearLevel_id','student_id','date_recorded','offense_count','punishment_id','remarks','violation_id', 'status','reported_by','evidence'];

    // public static $rules = [
    //     'date_recorded'=>'required',
    //   'remarks'=>'required',
    //   'student_id'=>'required',
    //   'violation_id'=>'required',
    //   'guidance_id'=>'required',
    // ];

    public function students() 
    {
        return $this->belongsTo(Student::class,'student_id');
    }

    public function violations() 
    {
        return $this->belongsTo(Violations::class,'violation_id');
    }
    public function guidances() {
        return $this->belongsTo(Guidance::class, 'guidance_id');
    }
    public function punishments() {
        return $this->belongsTo(Punishments::class, 'punishment_id');
    }
    public function getSearchResult(): SearchResult
    {
       $url = url('studentrecord/'.$this->id);
    
        return new \Spatie\Searchable\SearchResult(
           $this,
           $this->remarks,
           $this->student_id,
           $this->violation_id,
           $this->guidance_id,

           $url
           );
    }

    public function users() 
    {
        return $this->belongsTo(User::class,'reported_by');
    }

}
