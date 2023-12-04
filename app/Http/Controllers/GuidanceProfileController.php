<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Guiance;

class GuidanceProfileController extends Controller
{
    public function guidanceprofile(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->load('guidance');
            $guidance = $user->guidance;

            if ($student) {
                \Log::info('Guidance Data:', $guidance->toArray());

                $imageUrl = url('storage/' . $guidance->guidance_img);

                return response()->json([
                    'guidance_id' => $guidance->id,
                    'fname' => $guidance->fname,
                    'lname' => $guidance->lname,
                    'guidance_img' => $imageUrl, 
                ]);
            } else {
                return response()->json(['error' => 'Guidance profile not found for this user'], 404);
            }
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }
}
