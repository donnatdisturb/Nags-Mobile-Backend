<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpEmail; 
use App\Models\Student;
use App\Models\StudentFamily;
use App\Models\Teacher;
use App\Models\User;
use Hash;


class AuthController extends Controller
{
    public function _construct()
    {
        $this->middleware('auth:api',['except'=>['login']]);
    }
    
    public function login(Request $request)
    {
        Log::info('Login request received', [
            'email' => $request->input('email'),
            'user_agent' => $request->header('User-Agent'),
        ]);
    
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        $user = auth()->user();
    
        
        $role = $user->role; 
    
        switch ($role) {
            case 'admin':
                
                return response()->json([
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                    'user' => auth('api')->user(),
                    'role' => 'admin',
                ]);

                break;
    
            case 'student':
                return response()->json([
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                    'user' => auth('api')->user(),
                    'role' => 'student',
                ]);
                break;
 
            case 'parent':
                return response()->json([
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                    'user' => auth('api')->user(),
                    'role' => 'parent',
                ]);
                break;
                
            
            case 'teacher':
                return response()->json([
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                    'user' => auth('api')->user(),
                    'role' => 'teacher',
                ]);
                break;
    
            default:
                return response()->json(['error' => 'Invalid role'], 403);
        }
    }
    
    public function loginOTP(Request $request)
    {
        Log::info('Login request received', [
            'email' => $request->input('email'),
            'user_agent' => $request->header('User-Agent'),
        ]);
        
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $user = auth()->user();
        $role = $user->role;
        
        switch ($role) {
            case 'admin':
                break;

            case 'student':
                $student = $user->student;
                if ($student) {
                    $otp = rand(100000, 999999);
                    $student->otp = $otp;
                    $student->save();
                    
                    Mail::to($user->email)->send(new OtpEmail($otp));
                    return response()->json([
                        'message' => 'OTP sent successfully',
                        'role' => $role,
                    ]);
                } 
                else 
                {
                    return response()->json(['error' => 'Student record not found'], 404);
                }
                break;
                
            case 'parent':
                $parent = $user->studentFamily;
                
                if ($parent) {
                    $otp = rand(100000, 999999);
                    $parent->otp = $otp;
                    $parent->save();
                    
                    Mail::to($user->email)->send(new OtpEmail($otp));
                    return response()->json([
                        'message' => 'OTP sent successfully',
                        'role' => $role,
                    ]);
                } 
                else 
                {
                    return response()->json(['error' => 'Parent record not found'], 404);
                }
                break;
                
            case 'teacher':
                $teacher = $user->teacher;
                
                if ($teacher) {
                    $otp = rand(100000, 999999);
                    $teacher->otp = $otp;
                    $teacher->save();
                    
                    Mail::to($user->email)->send(new OtpEmail($otp));
                    return response()->json([
                        'message' => 'OTP sent successfully',
                        'role' => $role,
                    ]);
                } 
                else 
                {
                    return response()->json(['error' => 'teacher record not found'], 404);
                }
                break;
                default:
                return response()->json(['error' => 'Invalid role'], 403);
            }
        }

public function verifyOtp(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'otp' => 'required|string|min:6',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    $user = User::where('email', $request->input('email'))->first();

    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

    $role = $user->role;
    \Log::info("Role: $role");

    switch ($role) {
        case 'student':
            $student = $user->student;

            if (!$student || $request->input('otp') != $student->otp) {
                return response()->json(['error' => 'Invalid OTP'], 401);
            }

            $token = auth('api')->login($user);

            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'user' => auth('api')->user(),
                'role' => 'student',
            ]);
            break;

        case 'parent':
            $studentFamily = $user->studentFamily;
            

            if (!$studentFamily || $request->input('otp') != $studentFamily->otp) {
                return response()->json(['error' => 'Invalid OTP'], 401);
            }

            $token = auth('api')->login($user);

            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'user' => auth('api')->user(),
                'role' => 'parent',
            ]);
            break;

        case 'teacher':
            $teacher = $user->teacher;

            if (!$teacher || $request->input('otp') != $teacher->otp) {
                return response()->json(['error' => 'Invalid OTP'], 401);
            }

            $token = auth('api')->login($user);

            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'user' => auth('api')->user(),
                'role' => 'teacher',
            ]);
            break;
            break;

        default:
            return response()->json(['error' => 'Invalid role'], 403);
    }
}

    public function profile()
    {
        $user = auth()->user(); 
        return response()->json(['profile' => $user]);
    }

public function logout(Request $request)
{
    Log::info('Logout method called');

    if (auth()->check()) {
        $user = auth()->user();
        if ($user->student) {
            Auth()->logout();
        }
    }

    return response()->json(['message' => 'Successfully logged out']);
}

public function logoutparent(Request $request){
    Log::info('Logout method called');

    if (auth()->check()) {
        $user = auth()->user();
        if ($user->parent) {
            Auth()->logout();
        }
    }

    return response()->json(['message' => 'Successfully logged out']);

}

public function logoutteacher(Request $request){
    Log::info('Logout method called');

    if (auth()->check()) {
        $user = auth()->user();
        if ($user->teacher) {
            Auth()->logout();
        }
    }

    return response()->json(['message' => 'Successfully logged out']);

}




}
