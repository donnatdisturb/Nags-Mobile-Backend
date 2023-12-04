<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\StudentFamily;
use Illuminate\Support\Facades\Log; // Import the Log class
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class ParentProfileController extends Controller
{
    public function profileparent(Request $request)
    {
        $validator = Validator::make($request->all(), [
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $user = $request->user();

        if ($user) {
            $studentFamily = StudentFamily::where('user_id', $user->id)->first();
            $imageUrl = asset('storage/' . $studentFamily->family_img);


            if ($studentFamily) {
                Log::info('Student Family Data:', $studentFamily->toArray());

                return response()->json([
                    'email' => $user->email,
                    'fname' => $studentFamily->fname,
                    'lname' => $studentFamily->lname,
                    'phone' => $studentFamily->phone,
                    'address' => $studentFamily->address,
                    'family_img' => $imageUrl,

                ]);
            } else {
                Log::error('Student family profile not found for this user');
                return response()->json(['error' => 'Student family profile not found for this user'], 404);
            }
        } else {
            Log::error('User not found');
            return response()->json(['error' => 'User not found'], 404);
        }
    }
//     public function updateparent(Request $request)
// {
//     try {
//         $validator = Validator::make($request->all(), [
//             'fname' => 'required|string',
//             'lname' => 'required|string',
//             'address' => 'required|string',
//             'phone' => 'required|string',
//         ]);

//         if ($validator->fails()) {
//             throw new ValidationException($validator);
//         }

//         $user = $request->user();

//         if ($user) {
//             $studentFamily = StudentFamily::where('user_id', $user->id)->first();


//             $studentFamily->fname = $request->input('fname');
//             $studentFamily->lname = $request->input('lname');
//             $studentFamily->address = $request->input('address');
//             $studentFamily->phone = $request->input('phone');

//             $studentFamily->save();

//             return response()->json([
//                 'status' => 'success',
//                 'message' => 'Profile updated successfully.',
//             ]);
//         } else {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'User not authenticated.',
//             ]);
//         }
//     } catch (ValidationException $e) {
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Validation error.',
//             'errors' => $e->errors(),
//         ]);
//     } catch (\Exception $e) {
//         \Log::error('Error updating profile: ' . $e->getMessage());
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Error updating profile ',
//         ]);
//     }
// }
public function updateparent(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fname' => 'required|string',
                'lname' => 'required|string',
                'address' => 'required|string',
                'phone' => 'required|string',
                'uploads' => 'required|string',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $user = $request->user();

            if ($user) {
                $studentFamily = StudentFamily::where('user_id', $user->id)->first();

                $studentFamily->fname = $request->input('fname');
                $studentFamily->lname = $request->input('lname');
                $studentFamily->address = $request->input('address');
                $studentFamily->phone = $request->input('phone');

                $imageData = $request->input('uploads');
                $imgData = base64_decode($imageData);

                if ($imgData === false) {
                    throw new \Exception('Invalid base64 image data.');
                }

                $imgFileName = 'family_img_' . time() . '.jpg';
                Storage::disk('public')->put('images/' . $imgFileName, $imgData);

                $imgPath = 'images/' . $imgFileName;
                $studentFamily->family_img = $imgPath;
                $studentFamily->save();

                Log::info('Profile and image updated successfully. Image Path: ' . $imgPath);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Profile and image updated successfully.',
                    'family_img' => $imgPath,
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
            Log::error('Error updating profile and saving image: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating profile and saving image.',
            ]);
        }
 }}

