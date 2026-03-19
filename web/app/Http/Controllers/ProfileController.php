<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function update(UpdateProfileRequest $request)
    {
        $user = Auth::user();
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        if($request->email){
            $user->email = $request->email;
        }
        if($request->name){
            $user->name = $request->name;
        }
        $user->save();

        
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user
        ], 200);
    }
}
