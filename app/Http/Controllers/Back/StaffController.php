<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Staff;
use App\Models\Role;
use App\Models\User;
use Hash;
use Session;

class StaffController extends Controller
{
    //
    public function index()
    {
        $staffs = Staff::all();
        return view('admin.staff.index', compact('staffs'));
    }
    public function create()
    {
        $roles = Role::all();
        return view('admin.staff.create', compact('roles'));
    }

    public function store(Request $request)
    {   
        if(User::where('email', $request->email)->first() == null){
            $user = new User;
            $user->name = $request->name;
            $user->username = $request->username;
            $user->email = $request->email;
            $user->phone = $request->mobile;
            $user->user_role = "staff";
            $user->password = Hash::make($request->password);
            if($user->save()){
                $staff = new Staff;
                $staff->user_id = $user->id;
                $staff->role_id = $request->role_id;
                if($staff->save()){
                    return redirect()->route('admin.staffs.index')->withSuccess('Staff has been inserted successfully');
                }
            }
        }

        return back()->withError('Email already used');
    }

    public function edit($id)
    {
        $staff = Staff::findOrFail($id);
        $roles = Role::all();
        return view('admin.staff.edit', compact('staff', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);
        $user = $staff->user;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->mobile;
        if(strlen($request->password) > 0){
            $user->password = Hash::make($request->password);
        }
        if($user->save()){
            $staff->role_id = $request->role_id;
            if($staff->save()){
                return redirect()->route('admin.staffs.index')->withSuccess(__('Staff has been updated successfully'));
            }
        }

        
        return back()->withError(__('Something went wrong'));
                
    }

    public function destroy($id)
    {
        $user = User::find(Staff::findOrFail($id)->user->id);
        $user->user_role = "user";
        $user->save();
        if(Staff::destroy($id)){
            return redirect()->route('admin.staffs.index')->withSuccess( __('Staff has been deleted successfully'));
        }

        return back()->withError(__('Something went wrong'));
    }

    // Roles
    public function roles()
    {
        $roles = Role::all();
        return view('admin.staff.roles.index', compact('roles'));
    }
    public function create_role()
    {
        return view('admin.staff.roles.create');
    }

    public function store_role(Request $request)
    {
        if($request->has('permissions')){
            $role = new Role;
            $role->name = $request->name;
            $role->permissions = json_encode($request->permissions);
            $role->save();

            return redirect()->route('admin.roles.index')->withSuccess(__('Role has been inserted successfully'));
        }
        
        return back()->withError(__('Something went wrong'));

    }

    public function edit_role(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        return view('admin.staff.roles.edit', compact('role'));
    }

    public function update_role(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        if($request->has('permissions')){
            $role->name = $request->name;
            $role->permissions = json_encode($request->permissions);
            $role->save();

            return redirect()->route('admin.roles.index')->withSuccess('Role has been updated successfully');
        }
        
        return back()->withError('Something went wrong');
    }

    public function destroy_role($id)
    {
        $role = Role::findOrFail($id);
        Role::destroy($id);
        return redirect()->route('admin.roles.index')->withSuccess(__('Role has been deleted successfully'));
    }
}
