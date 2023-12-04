<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Counsel;
use App\Models\Guidance;
use App\Models\Student;
use App\Models\StudentFamily;
use Auth;
use Illuminate\Support\Facades\Log;
use DB;

class CoachingController extends Controller
{
    public function __construct()
{
    $this->middleware('auth');
}

public function checkrecords()
{
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->student) {
            $studentId = $user->student->id;
            $userID = $user->id;

            $currentDate = now();
            $record = Counsel::where('createdby', $userID)
                ->where('Status', '=', 'PENDING')
                ->get();

            if ($record->count() > 0) {
                return response()->json(['success' => false, 'message' => 'You still have pending schedule']);
            } else {
                return response()->json(['success' => true, 'message' => 'You can create new schedule']);
            }
        }

        if ($user->studentFamily) {
            $userID = $user->id;

            $currentDate = now();
            $record = Counsel::where('createdby', $userID)
                ->where('Status', '=', 'PENDING')
                ->get();

            if ($record->count() > 0) {
                return response()->json(['success' => false, 'message' => 'You still have pending schedule']);
            } else {
                return response()->json(['success' => true, 'message' => 'You can create new schedule']);
            }
        }
    }
    return response()->json(['success' => false, 'message' => 'Invalid user or parent not found']);
}

public function records(){
    
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->student) {
            $studentId = $user->student->id;
            $userID = $user->id;
            \Log::info("user ID: $userID");

            \Log::info("Student ID: $studentId");

            $currentDate = now(); 
            $schedules = Counsel::join('guidances', 'counsil.guidance_id', '=', 'guidances.id')
            ->where('counsil.student_id', $studentId)
            ->where('counsil.scheduled_date', '>', $currentDate)
            ->where('createdby',$userID)
            ->select('counsil.*', 'guidances.fname as guidance_fname', 'guidances.lname as guidance_lname')
            ->get();

            return response()->json([
                'success' => true,
                'schedules' => $schedules,
            ]);
        } else {
            return response()->json(['success' => false, 'message' => 'ERROR']);
        }
    }

    return response()->json(['success' => false, 'message' => 'User not authenticated']);
}

public function parentrecords()
{
    if (auth()->check()) {
        $user = auth()->user();
        \Log::info("user ID: $user->id");

        if ($user->studentFamily) {
            $userID = $user->id;
            \Log::info("User ID: $userID");

            $currentDate = now();
            
            $schedules = Counsel::join('guidances', 'counsil.guidance_id', '=', 'guidances.id')
                ->join('students', 'counsil.student_id', '=', 'students.id')
                ->join('studentfamilies', 'students.family_id', '=', 'studentfamilies.id')
                ->where('counsil.scheduled_date', '>', $currentDate)
                ->where('counsil.createdby', $userID)
                ->where('studentfamilies.user_id', $userID)
                ->select('counsil.*', 'guidances.fname as guidance_fname', 'guidances.lname as guidance_lname', 'students.fname as student_fname',  // Add this line for student first name
                'students.lname as student_lname')
                ->get();

            return response()->json([
                'success' => true,
                'schedules' => $schedules,
            ]);
        } else {
            return response()->json(['success' => false, 'message' => 'User is not a parent']);
        }
    }

    return response()->json(['success' => false, 'message' => 'User not authenticated']);
}

public function create()
{
    if (auth()->check()) {
        $user = auth()->user();

        if ($user->student) {
            $studentId = $user->student->id;
            $userID = $user->id;
           
            \Log::info("Student ID: $studentId");

            $guidances = Guidance::all();
            $allCounselSchedules = Counsel::all();

            $guidanceData = $guidances->map(function ($guidance) {
                return [
                    'id' => $guidance->id,
                    'fname' => $guidance->fname,
                    'lname' => $guidance->lname,
                ];
            });

            $counselScheduleData = $allCounselSchedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'student_id' => $schedule->student_id, 
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'scheduled_date' => $schedule->scheduled_date,
                ];
            });

            return response()->json([
                'success' => true,
                'guidances' => $guidanceData,
                'student_id' => $studentId,
                'authUser' => $userID,
                'CoachingSchedules' => $counselScheduleData
            ]);
        } else {
            return response()->json(['success' => false, 'message' => 'User is not associated with a student']);
        }
    }

    return response()->json(['success' => false, 'message' => 'User not authenticated']);
}

public function create2()
{
    if (auth()->check()) {
        $user = auth()->user();
        $studentFamily = $user->studentFamily;

        if ($user->studentFamily) {
            $ParentId = $user->studentFamily->id;
            $userID = $user->id;
           
            \Log::info("Parent ID: $ParentId");
            
            \Log::info("Parent ID: $userID");

            $students = Student::where('family_id',$ParentId)->get();

            $guidances = Guidance::all();

            $guidanceData = $guidances->map(function ($guidance) {
                return [
                    'id' => $guidance->id,
                    'fname' => $guidance->fname,
                    'lname' => $guidance->lname,
                ];
            });

            $studentsData = $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'fname' => $student->fname, 
                    'lname' => $student->lname, 
                ];
            });

            return response()->json([
                'success' => true,
                'guidances' => $guidanceData,
                'students' => $studentsData,
                'authUser' => $userID

            ]);
        } else {
            return response()->json(['success' => false, 'message' => 'User is not associated with a parent']);
        }
    }

    return response()->json(['success' => false, 'message' => 'User not authenticated']);
}


public function store(Request $request)
{
    Log::info('Received request to store coaching:', ['headers' => $request->headers->all(), 'body' => $request->all()]);

    try {
        $validatedData = $request->validate([
            'scheduled_date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'guidance_id' => 'required|exists:guidances,id', 
            'student_id' => 'required|exists:students,id',
            'createdby'=> 'required|exists:users,id'
        ]);

        $validatedData['Status'] = 'pending';
        $counsel = Counsel::create($validatedData);

        Log::info('Counsel created successfully', ['data' => $counsel]);

        return response()->json(['success' => true, 'message' => 'Counsel created successfully', 'data' => $counsel], 201);
    } catch (\Exception $e) {
        Log::error('Error creating coaching schedule: ' . $e->getMessage());
        Log::error($e->getTraceAsString());
    
        return response()->json(['success' => false, 'message' => 'Error creating coaching schedule'], 500);
    }
    
}

// public function store2(Request $request)
// {
//     Log::info('Received request to store coaching:', ['headers' => $request->headers->all(), 'body' => $request->all()]);

//     try {
//         $validatedData = $request->validate([
//             'scheduled_date' => 'required|date_format:Y-m-d',
//             'start_time' => 'required|date_format:H:i',
//             'end_time' => 'required|date_format:H:i',
//             'guidance_id' => 'required|exists:guidances,id', 
//             'student_id' => 'required|exists:students,id',
//         ]);

//         $validatedData['Status'] = 'pending';
//         $counsel = Counsel::create($validatedData);

//         Log::info('Counsel created successfully', ['data' => $counsel]);

//         return response()->json(['success' => true, 'message' => 'Counsel created successfully', 'data' => $counsel], 201);
//     } catch (\Exception $e) {
//         Log::error('Error creating coaching schedule: ' . $e->getMessage());
//         Log::error($e->getTraceAsString());
    
//         return response()->json(['success' => false, 'message' => 'Error creating coaching schedule'], 500);
//     }
    
// }
public function store2(Request $request)
{
    Log::info('Received request to store coaching:', ['headers' => $request->headers->all(), 'body' => $request->all()]);

    try {
        $validatedData = $request->validate([
            'scheduled_date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'guidance_id' => 'required|exists:guidances,id', 
            'student_id' => 'required|exists:students,id',
            'createdby'=> 'required|exists:users,id'
        ]);

        $validatedData['Status'] = 'pending';
        
        // Create a counseling entry for each selected student
        // foreach ($validatedData['student_ids'] as $studentId) {
        //     $counsel = Counsel::create([
        //         'scheduled_date' => $validatedData['scheduled_date'],
        //         'start_time' => $validatedData['start_time'],
        //         'end_time' => $validatedData['end_time'],
        //         'guidance_id' => $validatedData['guidance_id'],
        //         'student_id' => $studentId,
        //         'Status' => $validatedData['Status'],
        //     ]);
        $counsel = Counsel::create($validatedData);

        Log::info('Counsel created successfully', ['data' => $counsel]);

        return response()->json(['success' => true, 'message' => 'Counsel created successfully', 'data' => $counsel], 201);
    } catch (\Exception $e) {
        Log::error('Error creating coaching schedule: ' . $e->getMessage());
        Log::error($e->getTraceAsString());
    
        return response()->json(['success' => false, 'message' => 'Error creating coaching schedule'], 500);
    }
}

public function cancelTransaction($id)
{
    $counsel = Counsel::find($id);

        if (!$counsel) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        $counsel->delete();

        return response()->json(['message' => 'Transaction canceled successfully']);
    }

    public function cancelTransactionParent($id)
{
    $counsel = Counsel::find($id);

        if (!$counsel) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        $counsel->delete();

        return response()->json(['message' => 'Transaction canceled successfully']);
    }

}
