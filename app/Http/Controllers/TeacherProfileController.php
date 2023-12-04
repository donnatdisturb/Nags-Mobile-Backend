<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TeacherProfileController extends Controller
{
    public function profileteacher(Request $request)
    {
        $validator = Validator::make($request->all(), [
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $user = $request->user();

        if ($user) {
            $teacher = Teacher::where('user_id', $user->id)->first();

            if ($teacher) {
                Log::info('Teacher Data:', $teacher->toArray());

                return response()->json([
                    'email' => $user->email,
                    'fname' => $teacher->fname,
                    'lname' => $teacher->lname,
                ]);
            } else {
                Log::error('Teacher profile not found for this user');
                return response()->json(['error' => 'Teacher profile not found for this user'], 404);
            }
        } else {
            Log::error('User not found');
            return response()->json(['error' => 'User not found'], 404);
        }
    }
    public function updateteacher(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'fname' => 'required|string',
            'lname' => 'required|string',

        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $user = $request->user();

        if ($user) {
            $teacher = teacher::where('user_id', $user->id)->first();


            $teacher->fname = $request->input('fname');
            $teacher->lname = $request->input('lname');

            $teacher->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully.',
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated.',
            ]);
        }
    } catch (ValidationException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation error.',
            'errors' => $e->errors(),
        ]);
    } catch (\Exception $e) {
        \Log::error('Error updating profile: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Error updating profile ',
        ]);
    }
}


}
