<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AvatarController extends Controller
{
    public function upload(Request $request)
    {
        $user = Auth::user();
        abort_if(!$user, 401);
        $request->validate(['avatar' => 'required|image|max:2048']);

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }
        $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
        $user->save();

        return back()->with('asset_status', 'Avatar updated.');
    }

    public function delete()
    {
        $user = Auth::user();
        abort_if(!$user, 401);
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->avatar_path = null;
            $user->save();
        }
        return back()->with('asset_status', 'Avatar removed.');
    }
}
