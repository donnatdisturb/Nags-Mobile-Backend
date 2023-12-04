<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Guidance;
use App\Models\Student;
use App\Models\StudentRecords;
use App\Models\StudentFamily;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\GoodMoral;
use App\Models\Punishments;

use App\Mail\ContactGuidance1;
use App\Mail\ContactStudent1;
use App\Mail\ContactStudent5;

use App\Events\SendMail;
use Event;
use View;
use Redirect;
use DB;
use Mail;
use Hash;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }
    
//     public function dashboard(Request $request)
// {
//     $user = Auth::user();

//     if (!$user) {
//         return response()->json(['error' => 'Unauthorized'], 401);
//     }

//     $role = $user->role;

//     switch ($role) {
//         case 'admin':
//             $studentRecords = StudentRecords::all();
//             break;

//         case 'guidance':
//             $studentRecords = StudentRecords::where('guidance_id', $user->guidance->id)->get();
//             break;
           
//             case 'student':
//                 $studentRecords = StudentRecords::where('student_id', $user->student->id)
//                     ->with(['students', 'violations', 'guidances'])
//                     ->get();
//                 break;
//         default:
//             return response()->json(['error' => 'Invalid role'], 403);
//     }

//     \Log::info('API Response: ' . json_encode(['data' => $studentRecords]));

//     return response()->json(['data' => $studentRecords]);
// }
///REALLLL CODE
// public function dashboard(Request $request)
// {
//     $user = Auth::user();

//     if (!$user) {
//         return response()->json(['error' => 'Unauthorized'], 401);
//     }

//     $role = $user->role;

//     switch ($role) {
//         case 'admin':
//             // For admin, return all Student Records
//             $studentRecords = StudentRecords::all();
//             break;

//         case 'guidance':
//             // For guidance, return Student Records related to their guidance responsibilities
//             $studentRecords = StudentRecords::where('guidance_id', $user->guidance->id)->get();
//             break;

//         case 'student':
//             // For student, return all their Student Records with related data
//             $studentRecords = StudentRecords::where('student_id', $user->student->id)
//                 ->with(['students', 'violations', 'guidances'])
//                 ->get();
//             break;

//         case 'parent':
//             // For parent, return all Student Records
//             $studentFamily = StudentFamily::where('user_id', $user->id)->first();
//             $studentID = Student::where('family_id', $studentFamily->id)->first();
//             $studentRecords = StudentRecords::where('student_id', $studentID->id)
//             ->with(['students', 'violations', 'guidances'])

//                 ->get();

//             // Handle empty results
//             if ($studentRecords->isEmpty()) {
//                 Log::info('No student records found for this parent.');
//                 return response()->json(['message' => 'No student records found for this parent'], 404);
//             }

//             break;

//         default:
//             // Invalid role
//             Log::error('Invalid role detected.');
//             return response()->json(['error' => 'Invalid role'], 403);
//     }

//     // Log the response before sending it
//     Log::info('API Response: ' . json_encode(['data' => $studentRecords->toArray(), 'role' => $role]));

//     return response()->json(['data' => $studentRecords->toArray(), 'role' => $role]);
// }
// public function dashboard(Request $request)
// {
//     $user = Auth::user();

//     if (!$user) {
//         return response()->json(['error' => 'Unauthorized'], 401);
//     }

//     $role = $user->role;

//     switch ($role) {
//         case 'admin':
//             $studentRecords = StudentRecords::all();
//             break;
//         case 'guidance':
//             $studentRecords = StudentRecords::where('guidance_id', $user->guidance->id)->get();
//             break;
//         case 'student':
//             $studentRecords = StudentRecords::where('student_id', $user->student->id)
//                 ->with(['students', 'violations', 'guidances', 'punishments'])
//                 ->get();
//             break;
//         case 'parent':
//             $studentFamily = StudentFamily::where('user_id', $user->id)->first();

//             if ($studentFamily) {
//                 $familyId = $studentFamily->id;

//                 $studentIDs = Student::where('family_id', $familyId)->pluck('id');

//                 $studentRecords = StudentRecords::whereIn('student_id', $studentIDs)
//                     ->with(['students', 'violations', 'guidances', 'punishments'])
//                     ->get();

//                 if ($studentRecords->isEmpty()) {
//                     Log::info('No student records found for this parent.');
//                     return response()->json(['message' => 'No student records found for this parent'], 404);
//                 }
//             } else {
//                 Log::info('No student family found for this parent.');
//                 return response()->json(['message' => 'No student family found for this parent'], 404);
//             }

//             break;

//         default:
//             Log::error('Invalid role detected.');
//             return response()->json(['error' => 'Invalid role'], 403);
//     }

//     Log::info('API Response: ' . json_encode(['data' => $studentRecords->toArray(), 'role' => $role]));

//     return response()->json(['data' => $studentRecords->toArray(), 'role' => $role]);
// }
public function dashboard(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $role = $user->role;

    switch ($role) {
        case 'admin':
            $studentRecords = StudentRecords::all();
            break;
        case 'guidance':
            $studentRecords = StudentRecords::where('guidance_id', $user->guidance->id)->get();
            break;
        case 'student':
            $studentRecords = StudentRecords::where('student_id', $user->student->id)
                ->with(['students', 'violations', 'guidances', 'punishments'])
                ->get();
            break;
        case 'parent':
            $studentFamily = StudentFamily::where('user_id', $user->id)->first();

            if ($studentFamily) {
                $familyId = $studentFamily->id;

                $studentIDs = Student::where('family_id', $familyId)->pluck('id');

                $studentRecords = StudentRecords::whereIn('student_id', $studentIDs)
                    ->with(['students', 'violations', 'guidances', 'punishments'])
                    ->get();

                if ($studentRecords->isEmpty()) {
                    Log::info('No student records found for this parent.');
                    return response()->json(['message' => 'No student records found for this parent'], 404);
                }
            } else {
                Log::info('No student family found for this parent.');
                return response()->json(['message' => 'No student family found for this parent'], 404);
            }

            break;

        default:
            Log::error('Invalid role detected.');
            return response()->json(['error' => 'Invalid role'], 403);
    }

    $studentRecordsData = [];

    foreach ($studentRecords as $record) {
        $studentName = $record->students->fname;

        $data = [
            'dateRecorded' => $record->date_recorded,
            'remarks' => $record->remarks,
            'status' => $record->status,
            'punishmentName' => $record->punishments->name,
            'violationName' => $record->violations->name,
            'guidanceName' => $record->guidances->fname,
            'studentName' => $studentName,
        ];

        $studentRecordsData[] = $data;
    }

    Log::info('API Response: ' . json_encode(['data' => $studentRecordsData, 'role' => $role]));

    return response()->json(['data' => $studentRecordsData, 'role' => $role]);
}

// public function dashboard2(Request $request)
// {
//     $user = Auth::user();

//     if (!$user) {
//         return response()->json(['error' => 'Unauthorized'], 401);
//     }

//     $role = $user->role;

//     switch ($role) {
//         case 'parent':
//             // For parent, return all Student Records
//             $studentFamily = StudentFamily::where('user_id', $user->id)->first();
//             $studentID = Student::where('family_id',$studentFamily->id)->first();
//             $studentRecords = StudentRecords::where('student_id', $studentID->id)
//                 // ->with(['students', 'violations', 'guidances'])
//                 ->get();
//             // $studentRecords = $studentRecords::all();

//             // Handle empty results
//             if ($studentRecords->isEmpty()) {
//                 return response()->json(['message' => 'No student records found for this parent'], 404);
//             }

//             break;

//         default:
//             // Invalid role
//             return response()->json(['error' => 'Invalid role'], 403);
//     }

//     // Log the response before sending it
//     // \Log::info('API Response: ' . json_encode(['data' => $studentRecords->toArray()]));

//     return response()->json(['data' => $studentRecords->toArray()]);
//     // return response()->json(['data' => $studentID]);

// }
// public function dashboard2(Request $request)
// {
//     $user = Auth::user();

//     if (!$user) {
//         Log::error('Unauthorized access to dashboard2. User not authenticated.');
//         return response()->json(['error' => 'Unauthorized'], 401);
//     }

//     $role = $user->role;

//     switch ($role) {
//         case 'parent':
//             // For parent, return all Student Records
//             $studentFamily = StudentFamily::where('user_id', $user->id)->first();
//             $studentID = Student::where('family_id', $studentFamily->id)->first();
//             $studentRecords = StudentRecords::where('student_id', $studentID->id)
//                 // ->with(['students', 'violations', 'guidances'])
//                 ->get();
//             // $studentRecords = $studentRecords::all();

//             // Handle empty results
//             if ($studentRecords->isEmpty()) {
//                 Log::info('No student records found for this parent.');
//                 return response()->json(['message' => 'No student records found for this parent'], 404);
//             }

//             break;

//         default:
//             // Invalid role
//             Log::error('Invalid role detected.');
//             return response()->json(['error' => 'Invalid role'], 403);
//     }

//     // Log the response before sending it
//     Log::info('API Response: ' . json_encode(['data' => $studentRecords->toArray()]));

//     return response()->json(['data' => $studentRecords->toArray()]);
//     // return response()->json(['data' => $studentID]);

// }

}
