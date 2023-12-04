<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

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
                $imageUrl = asset('storage/' . $teacher->teacher_img);

                return response()->json([
                    'email' => $user->email,
                    'fname' => $teacher->fname,
                    'lname' => $teacher->lname,
                    'teacher_img' => $imageUrl,
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
            $imageData = $request->input('uploads');
            $imgData = base64_decode($imageData);

            if ($imgData === false) {
                throw new \Exception('Invalid base64 image data.');
            }

            $imgFileName = 'student_img_' . time() . '.jpg';
            
            Storage::disk('public')->put('images/' . $imgFileName, $imgData);
            
            $imgPath = 'images/' . $imgFileName;
            $teacher->teacher_img = $imgPath;

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
