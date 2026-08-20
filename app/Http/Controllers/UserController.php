<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;


class UserController extends Controller
{
    //recupérer tous les users
    public function index()
    {
        $users = User::all();

        $totalUsers = User::count();
        $activeUsers = User::where('status' , 'actif')->count();
        $inactiveUsers = User::where('status' , 'inactif')->count();
        $admins = User::where('role', 'Admin')->count();
        
        return view('administration.users.user' , compact(
            'users',
            'totalUsers',
            'activeUsers',
            'inactiveUsers',
            'admins'
            ));
    }
    //afficher un seul user
    public function show($id)
    {
        $user = User::findOrFail($id);

        return view('administration.users.show', compact('user'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('administration.users.edituser', compact('user'));
    }

    //modification
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string',
            'telephone' => 'required|string',
            'email' => 'required|email',
            'role' => 'required|string',
            'department' => 'required|string',
        ]);

        $user->update($data);

        return redirect()->back()->with('success', 'Utilisateur modifié.');
    }
    public function delete($id)
    {
        $user = User::findOrFail($id);

        return view('administration.users.deluser', compact('user'));
    }
    //supprimer
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        $user->delete();

        return redirect()->back()->with('success', 'Utilisateur supprimé.');
    }
    // désactiver
    public function changeStatus($id)
    {
        $user = User::findOrFail($id);

        $user->status = $user->status === 'actif'
            ? 'inactif'
            : 'actif';

        $user->save();

        return redirect()->back();
    }
}
