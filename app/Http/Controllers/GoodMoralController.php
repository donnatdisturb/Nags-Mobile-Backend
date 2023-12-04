<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Guidance;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\GoodMoral;
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

class GoodMoralController extends Controller
{
    public function _construct()
    {
        $this->middleware('auth:api',['except'=>['login']]);
    }
    
    // public function index(Request $request)
    // {
    //     $goodMorals = GoodMoral::all();
    // // return response()->json(['data' => $goodMorals]);
    // return $goodMorals;
    // }

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $role = $user->role;

            switch ($role) {
                case 'admin':
                    $goodMorals = GoodMoral::all();
                    break;

                case 'guidance':
                    $goodMorals =  GoodMoral::all();
                    break;

                case 'student':
                    $goodMorals = GoodMoral::where('student_id', $user->student->id)->get();
                    break;

                default:
                    return response()->json(['error' => 'Invalid role'], 403);
            }

            $token = auth('api')->login($user);

            return response()->json([
                'data' => $goodMorals,
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
            ]);
        }

        return response()->json(['error' => 'User not found'], 404);
    }
    // public function create(Request $request)
    // {
    //     $user = auth()->user();
        
    //     if ($user->role !== 'student') {
    //         return response()->json(['error' => 'Only students are allowed to create Good Moral requests.'], 403);
    //     }
        
    //     $studentId = $user->id;
    //     $student = Student::where('user_id', $studentId)->first();
        
    //     if (!$student) {
    //         return response()->json(['message' => 'Student information not found.', 'success' => false], 404);
    //     }
        
    //     // Check if the student already has an existing Good Moral request
    //     // $existingRequest = GoodMoral::where('student_id', $studentId)
    //     $existingRequest = GoodMoral::where('student_id', $user->id)

    //         ->where('status', 'pending')
    //         ->first();
        
    //     if ($existingRequest) {
    //         return response()->json(['message' => 'You already have a pending Good Moral request.', 'success' => false]);
    //     }
        
    //     // Create a new GoodMoral request
    //     $goodMoral = new GoodMoral();
    //     $goodMoral->description = 'Good Moral';
    //     $goodMoral->status = 'pending';
    //     $goodMoral->schedule_date = 'pending';
    //     // $goodMoral->student_id = $studentId;
    //     $goodMoral->student_id = $user->id;

    //     $goodMoral->save();
    
    //     // Include the bearer token in the response
    //     $token = auth('api')->login($user);
    
    //     return response()->json([
    //         'message' => 'Request has been sent!',
    //         'success' => true,
    //         'access_token' => $token,
    //         'token_type' => 'bearer',
    //         'expires_in' => auth('api')->factory()->getTTL() * 60,
    //     ]);
    // }
    
   // GoodMoralController.php

//    public function create()
//    {

//        $user = auth()->user();
   
//        if ($user->student) {
//            $studentId = $user->student->id;
//                \Log::info("Student ID: $studentId");

//            $goodMoral = new GoodMoral();
//            $goodMoral->description = 'Good Moral'; 
//            $goodMoral->status = 'Pending'; 
//            $goodMoral->schedule_date = now(); 
//            $goodMoral->student_id = $studentId;
   
//            $goodMoral->save();
   
//            return response()->json([
//                'success' => true,
//                'message' => 'Good Moral record created successfully',
//                'goodMoral' => $goodMoral,
//            ]);
//        } else {
//            return response()->json(['success' => false, 'message' => 'Error: User is not associated with a student']);
//        }
//    }
public function create()
{
    $user = auth()->user();

    if ($user->student) {
        $studentId = $user->student->id;
        
        $existingGoodMoral = GoodMoral::where('student_id', $studentId)->first();

        if ($existingGoodMoral) {
            return response()->json([
                'success' => false,
                'message' => 'Good Moral request is already in process',
            ]);
        }

        $goodMoral = new GoodMoral();
        $goodMoral->description = 'Good Moral'; 
        $goodMoral->status = 'Pending'; 
        $goodMoral->schedule_date = now(); 
        $goodMoral->student_id = $studentId;

        $goodMoral->save();

        return response()->json([
            'success' => true,
            'message' => 'Good Moral record created successfully',
            'goodMoral' => $goodMoral,
        ]);
    } else {
        return response()->json(['success' => false, 'message' => 'Error: User is not associated with a student']);
    }
}
public function delete(Request $request)
{
    $user = Auth::user();

    if ($user && $user->student) {
        $studentId = $user->student->id;

        GoodMoral::where('student_id', $studentId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Good Moral record deleted successfully',
        ]);
    } else {
        return response()->json(['success' => false, 'message' => 'Error: User is not associated with a student']);
    }
}
}
