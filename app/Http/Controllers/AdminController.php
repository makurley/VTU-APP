<?php
// app/Http/Controllers/AdminController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Method to show the swap report
    public function swapReport()
    {
        // Your logic to generate the report or data for the view
        return view('admin.reports.swap');
    }
     public function indexUsers()
    {
        // Fetch all users or any other logic
        $users = User::all();

        return view('admin.users.index', compact('users'));
    }
     public function viewUser(User $user)
    {
        // You can customize this to fetch user details or handle any logic
        return view('admin.users.view', compact('user'));
    }
    public function index()
{
    $user = auth()->user(); // or fetch the user based on your logic

    return view('admin.index', compact('user'));
}

}
