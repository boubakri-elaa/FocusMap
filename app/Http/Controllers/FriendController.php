<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FriendController extends Controller
{
    public function addFriend(Request $request)
    {
        $friend = User::findOrFail($request->friend_id);
        Auth::user()->friends()->attach($friend->id);
        return response()->json(['message' => 'Ami ajouté avec succès']);
    }

    public function removeFriend(Request $request)
    {
        $friend = User::findOrFail($request->friend_id);
        Auth::user()->friends()->detach($friend->id);
        return response()->json(['message' => 'Ami supprimé']);
    }

    public function index()
    {
        $friends = Auth::user()->friends;
        return view('friends.index', compact('friends'));
    }
}