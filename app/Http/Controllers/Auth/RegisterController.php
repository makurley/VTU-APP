<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UserController;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }
    public function user_register(Request $request)
    {
        $request->ref;
        // check if user exists
        if($request->ref){
            $refer = User::whereUsername($request->ref)->first()->username;
            if($refer == null){
                return redirect()->route('user.register')->withError('Invalid referral link');
            }
            return view('auth.register' ,compact('refer'));
        }
        $refer = null;
        return view('auth.register' ,compact('refer'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'string|required|max:100',
            'email' => 'email|unique:users|string|required',
            'username' => 'string|unique:users|max:20|required|regex:/\w*$/|alpha_dash',
              'address' => 'required|string|numeric|min:10|unique:users,address',
            'phone' => 'required|string|numeric|min:10|unique:users,phone',
            'password' =>'required|string|min:8',
            'policy' =>  'required|string'
        ]);
        // check users
        if($request->referral){
            $refer = User::whereUsername($request->referral)->first();
            if($refer == null){
                return redirect()->route('index')->withError('Invalid referral link');
            }
            if($refer->username == null){
                return redirect()->route('index')->withError('Invalid referral link');
            }
        }
        $username = formatAndValidateUsername($request->username);
        if (!$username) {
            return back()->withInput()->withError('Invalid Username. Please change');
        }
        $user = new User();
        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->trxpin = $request->pin;
        $user->phone = $request->phone;
         $user->address = $request->address;
        $user->user_role = 'user';
        $user->password = Hash::make($request['password']);
        $user->ref_id = isset($request->referral) ?  $refer->id : 0;
        if(sys_setting('verify_email') == 0){
            $user->email_verified_at = date('Y-m-d H:m:s');
        }
        $user->save();
        event(new Registered($user));
        auth()->login($user);
        // generate virtual bank
        try {
           $bank = new UserController();
        //    $bank->generate_bank();
        } catch (\Exception $e) {
            // dd($e);
        }
        // welcome email
        if(\sys_setting('welcome_email') == 1){
            \send_emails($user->email, 'WELCOME_EMAIL',
            [
                'username' => $user['username'],
                'site_name' => get_setting('title'),
            ]);
        }
        // /send referral email// check users
        if($request->referral){
            $refer = User::whereUsername($request->referral)->first();
            if(\sys_setting('trx_email') == 1 && $refer->email_notify == 1){
                \send_emails($refer->email, 'REFERRAL_EMAIL',
                [
                    'username' => $refer['username'],
                    'site_name' => \get_setting('title'),
                    'refer_name' => $user['username'],
                    'date' => $user->created_at
                ]);
            }
        }
        return redirect()->route('user.index')->withSucess('Account Created Successful');
    }
    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
}
