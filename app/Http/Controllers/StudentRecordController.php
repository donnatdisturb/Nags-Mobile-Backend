<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Violations;
use App\Models\Guidance;
use App\Models\StudentRecords;
use App\Models\StudentFamily;
use App\Models\User;
use App\Models\Punishments;
use App\Models\YearLevel;
use App\Models\Section;
use App\Models\ViolationPunishment;
use App\Models\Offense;
use DB;
use Illuminate\Support\Facades\Log;
use Auth;
use Illuminate\Support\Facades\Storage;

class StudentRecordController extends Controller
{
    public function create()
{
    $guidances = Guidance::pluck('fname','id');
    $Yearlevel = YearLevel::pluck('Name','id');
    $sections = Section::pluck('sectionname','id');
    $violations = Violations::pluck('Statement','id');
    $studentsWithYearLevel = Student::select('id', 'fname','lname', 'YearLevel_id as year_level','section_id as section')->get();
    
    $studentData = $studentsWithYearLevel->reduce(function ($result, $student) {
        $result[$student->id] = [
            'fname' => $student->fname,
            'YearLevel_id' => $student->YearLevel_id,
        ];
        return $result;
    }, []);

    $user = auth()->user();

    return response()->json([
        'violations' => $violations,
        'studentData' => $studentData,
        'yearLevels' => $Yearlevel,
        'sections' => $sections,
        'authUser' => $user ? [
            'id' => $user->id,
        ] : [],
    ]);
}

    public function create1(){
        $Yearlevel = YearLevel::pluck('Name','id');
        $sections = Section::pluck('sectionname','id');
        $violations = Violations::pluck('Statement','id');
    
        $studentrecords = StudentRecords::all();
        $user = auth()->user();
        return response()->json([
            'yearlevels' => $Yearlevel,
            'sections' => $sections,
            'violations' => $violations,
            'authUser' => $user ? [
                'id' => $user->id,
            ] : [],
        ]);
    }

    public function getFilteredStudents(Request $request){
        $yearLevelId = $request->input('yearLevelId');
        $sectionId = $request->input('sectionId');
    
        $students = Student::when($yearLevelId, function ($query) use ($yearLevelId) {
                return $query->where('YearLevel_id', $yearLevelId);
            })
            ->when($sectionId, function ($query) use ($sectionId) {
                return $query->where('section_id', $sectionId);
            })
            ->get();
    
        return response()->json([
            'students' => $students
        ]);
    }
    

    // public function create()
    // {
    //     $guidances = Guidance::pluck('fname','id');
    //     $Yearlevel = YearLevel::pluck('Name','id');
    //     $sections = Section::pluck('sectionname','id');
    //     $violations = Violations::pluck('Statement','id');
    //     $studentsWithYearLevel = Student::select('id', 'fname','lname', 'YearLevel_id as year_level','section_id as section')->get();
        
    //     $studentData = $studentsWithYearLevel->reduce(function ($result, $student) {
    //         $result[$student->id] = [
    //             'fname' => $student->fname,
    //             'YearLevel_id' => $student->YearLevel_id,
    //         ];
    //         return $result;
    //     }, []);

    //     if (auth()->check()) {
    //     $user = auth()->user();
    //     \Log::info('ID: ' . $user);
    //     }

        
    // return response()->json([
    //     'violations' => $violations,
    //     'studentData' => $studentData,
    //     'yearLevels' => $Yearlevel,
    //     'sections' => $sections,
    //     'authUser' => [
    //         'id' => $user->id,
    //         'name' => $user->name,
           
    //     ],
    // ]);

    // }
   

    public function getOffenseLevel(Request $request)
    {
        $studentId = $request->input('studentId');
        $violationId = $request->input('violationId');
        $offenseLevel = $this->determineOffenseLevel($studentId, $violationId);
    
        
        $punishment = $this->getPunishmentForOffenseLevel($violationId, $offenseLevel);
        return response()->json(['offenseLevel' => $offenseLevel, 'punishment' => $punishment]);
    }
    
    

    private function determineOffenseLevel($studentId, $violationId)
    {   
        $offenseCount = StudentRecords::where('student_id', $studentId)
            ->where('violation_id', $violationId)
            ->count();

        if ($offenseCount == 0) {
            return 'First Offense';
        } elseif ($offenseCount == 1) {
            return 'Second Offense';
        } elseif ($offenseCount == 2) {
            return 'Third Offense';
        } else {
            return 'Subsequent Offense';
        }
    }

    private function getPunishmentForOffenseLevel($violationId, $offenseLevel)
    {   
    $offenseLevels = Offense::where('offensename', $offenseLevel)->first();
    if (!$offenseLevels) {
        return 'Offense level not found';
    }
    $offenseLevelId = $offenseLevels->id;
    
    $punishment = ViolationPunishment::where('offense_id', $offenseLevelId)
     ->where('violation_id', $violationId)
     ->first();
    if ($punishment) {
        return $punishment->punishment->name;
    } else {
        return 'No punishment found';
    }
}

public function store(Request $request)
{
    Log::info('Incoming Request:', [
        'method' => $request->method(),
        'path' => $request->path(),
        'data' => $request->all(),
    ]);

    $yearLevel = YearLevel::where('name', $request->input('year_level'))->first();
    $student = Student::where('fname', $request->input('student_Fname'))
    ->where('lname', $request->input('student_Lname'))
    ->first();
    $violations = Violations::where('statement', $request->input('violation'))->first();
    $dateRecorded = $request->input('date');
    $remarks = $request->input('remarks');
    $reportedby = $request->input('auth_user_id');


    if ($yearLevel && $student) {
       
        $yearLevelId = $yearLevel->id;
        $studentId = $student->id;
        $violationID = $violations->id;
        
        $countoffense = StudentRecords::where('student_id',$studentId)->where('violation_id',$violationID)->count();
        if($countoffense === 0){
            $offense = 'First Offense';
        }
        else if ($countoffense === 1){
            $offense = 'Second Offense';
        }
        else if ($countoffense === 2){
            $offense = 'Third Offense';
        }
        else{
            $offense = 'Subsequent Offense';
        }
        
        $Offenses = Offense::where('offensename',$offense)->first();
        $offenseID = $Offenses->id;
        $punishment = ViolationPunishment::where('offense_id',$offenseID)->where('violation_id',$violationID)->first();
        $punishmentId = $punishment->punishment_id;
        
        $imgPath = null;

        if ($request->has('uploads')) {
            try {
                $imageData = $request->input('uploads');
                $imgData = base64_decode($imageData);
                $imgFileName = 'violation_' . time() . '.jpg';
                Storage::put('public/images/' . $imgFileName, $imgData);
                $imgPath = 'public/images/' . $imgFileName;
    
                \Log::info('Image saved successfully. Path: ' . $imgPath);
            } catch (\Exception $e) {
                \Log::error('Error saving image: ' . $e->getMessage());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error saving image.',
                ]);
            }
        }
        
        $studentRecord = StudentRecords::create([
            'date_recorded' => $dateRecorded,
            'YearLevel_id' => $yearLevelId,
            'student_id' => $studentId,
            'violation_id' => $violationID,
            'remarks' => $remarks,
            'offense_count'=>$offense,
            'punishment_id'=>$punishmentId,
            'status' => 'PENDING',
            'reported_by' => $reportedby,
            'evidence' => $imgPath,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Violation record saved successfully.',
        ]);
    } 
    else if($yearLevel){
        $yearLevelId = $yearLevel->id;
        $violationID = $violations->id;

        $imgPath = null;

        if ($request->has('uploads')) {
            try {
                $imageData = $request->input('uploads');
                $imgData = base64_decode($imageData);
                $imgFileName = 'violation_' . time() . '.jpg';
                Storage::put('public/images/' . $imgFileName, $imgData);
                $imgPath = 'public/images/' . $imgFileName;
    
                \Log::info('Image saved successfully. Path: ' . $imgPath);
            } catch (\Exception $e) {
                \Log::error('Error saving image: ' . $e->getMessage());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error saving image.',
                ]);
            }
        }

        $studentRecord = StudentRecords::create([
            'date_recorded' => $dateRecorded,
            'YearLevel_id' => $yearLevelId,
            'violation_id' => $violationID,
            'remarks' => $remarks,
            'status' => 'PENDING',
            'reported_by' => $reportedby,
            'evidence' => $imgPath,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Violation record saved successfully.',
        ]);



    }

    return response()->json([
        'status' => 'error',
        'message' => 'Violation record is unsuccesfull.',
    ]);

}

public function store1(Request $request) {

    Log::info('Incoming Request:', [
        'method' => $request->method(),
        'path' => $request->path(),
        'data' => $request->all(),
    ]);

    $dateRecorded = $request->input('date');
    $yearLevelId = $request->input('yearlevel_id');
    $studentId = $request->input('student_id');
    $violations = Violations::where('statement', $request->input('violation'))->first();
    $violationID = $violations->id;
    $remarks = $request->input('remarks');
    $reportedby = $request->input('auth_user_id');

    $countoffense = StudentRecords::where('student_id',$studentId)->where('violation_id',$violationID)->count();
    if($countoffense === 0){
        $offense = 'First Offense';
    }
    else if ($countoffense === 1){
        $offense = 'Second Offense';
    }
    else if ($countoffense === 2){
        $offense = 'Third Offense';
    }
    else{
        $offense = 'Subsequent Offense';
    }

    $Offenses = Offense::where('offensename',$offense)->first();
    $offenseID = $Offenses->id;

    $punishment = ViolationPunishment::where('offense_id',$offenseID)->where('violation_id',$violationID)->first();
    $punishmentId = $punishment->punishment_id;

    $imgPath = null;

    if ($request->has('uploads')) {
        try {
            $imageData = $request->input('uploads');
            $imgData = base64_decode($imageData);
            $imgFileName = 'violation_' . time() . '.jpg';
            Storage::put('public/images/' . $imgFileName, $imgData);
            $imgPath = 'public/images/' . $imgFileName;

            \Log::info('Image saved successfully. Path: ' . $imgPath);
        } catch (\Exception $e) {
            \Log::error('Error saving image: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error saving image.',
            ]);
        }
    }


    $studentRecord = StudentRecords::create([
        'date_recorded' => $dateRecorded,
        'YearLevel_id' => $yearLevelId,
        'student_id' => $studentId,
        'violation_id' => $violationID,
        'remarks' => $remarks,
        'offense_count'=>$offense,
        'punishment_id'=>$punishmentId,
        'status' => 'PENDING',
        'reported_by' => $reportedby,
        'evidence' => $imgPath,
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Violation record saved successfully.',
    ]);
}

    
}
