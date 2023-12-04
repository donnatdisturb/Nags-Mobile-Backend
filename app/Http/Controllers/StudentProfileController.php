<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class StudentProfileController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user();
    
        if ($user) {
            $user->load('student');
            $student = $user->student;
    
            if ($student) {
                // Log the data for debugging
                \Log::info('Student Data:', $student->toArray());
    
                // Assuming 'student_img' contains the filename of the image
                $imageUrl = asset('storage/' . $student->student_img);
    
                return response()->json([
                    'student_id' => $student->id,
                    'fname' => $student->fname,
                    'lname' => $student->lname,
                    'student_img' => $imageUrl,
                    'sectioname' => $student->section->sectionname,
                    'yearname' => $student->yearlevel->Name,
                    'coursename' => $student->course->coursename,
                    'familyfname' => $student->studentfamily->fname,
                    'familylname' => $student->studentfamily->lname,
                    'email' => $student->user->email,
                ]);
    
            } else {
                return response()->json(['error' => 'Student profile not found for this user'], 404);
            }
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }
    
    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'uploads' => 'required|string', // Assuming 'uploads' is the base64-encoded image data
                'fname' => 'required|string',
                'lname' => 'required|string',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $user = $request->user();

            if ($user) {
                $user->load('student');
                $student = $user->student;

                // Update the profile fields based on your form input
                $student->fname = $request->input('fname');
                $student->lname = $request->input('lname');

                // Your existing image upload logic here
                $imageData = $request->input('uploads');
                $imgData = base64_decode($imageData);

                if ($imgData === false) {
                    throw new \Exception('Invalid base64 image data.');
                }

                $imgFileName = 'student_img_' . time() . '.jpg';
                
                // Save the image to the 'public' disk
                Storage::disk('public')->put('images/' . $imgFileName, $imgData);
                
                // Save the relative path to the student model (without 'public/')
                $imgPath = 'images/' . $imgFileName;
                $student->student_img = $imgPath;
                $student->save();

                \Log::info('Profile and image updated successfully. Image Path: ' . $imgPath);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Profile and image updated successfully.',
                    'student_img' => $imgPath,
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
            \Log::error('Error updating profile and saving image: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating profile and saving image.',
            ]);
        }
    }
}
