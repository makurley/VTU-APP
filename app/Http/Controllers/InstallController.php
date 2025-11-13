<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Setting;
use Hash;
use DB;

class InstallController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return view('install.index');
    }

    // Show agreement Page
    public function agreement()
    {
        return view('install.agreement');
    }

    public function requirement()
    {
        $requirements = config('install.extensions');

        $results = [];
        // Check the requirements
        foreach ($requirements as $type => $extensions) {
            if (strtolower($type) == 'php') {
                foreach ($requirements[$type] as $extensions) {
                    $results['extensions'][$type][$extensions] = true;

                    if (! extension_loaded($extensions)) {
                        $results['extensions'][$type][$extensions] = false;

                        $results['errors'] = true;
                    }
                }
            } elseif (strtolower($type) == 'apache') {
                foreach ($requirements[$type] as $extensions) {
                    // Check if the function exists
                    // Prevents from returning a false error
                    if (function_exists('apache_get_modules')) {
                        $results['extensions'][$type][$extensions] = true;

                        if (! in_array($extensions, apache_get_modules())) {
                            $results['extensions'][$type][$extensions] = false;

                            $results['errors'] = true;
                        }
                    }
                }
            } elseif (strtolower($type) == 'files') {
                foreach ($requirements[$type] as $extensions) {
                    // Check if the file directory exists
                    // Prevents from returning a false error
                    if (is_writable(base_path($extensions))) {
                        $results['extensions'][$type][$extensions] = true;
                    } else {
                        $results['extensions'][$type][$extensions] = false;
                        $results['errors'] = true;
                    }
                }
            }
        }

        // If the current php version doesn't meet the requirements
        if (version_compare(PHP_VERSION, config('install.php_version')) == -1) {
            $results['errors'] = true;
        }

        return view('install.requirement', compact('results'));   
    }

    public function database()
    {        
        return view('install.database');
    }

     /**
     * Validate the database credentials, and write the .env config file
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveDatabase(Request $request)
    {        
        // validate requests
        $validateDatabase = $this->validateDatabaseCredentials();
        if ($validateDatabase !== true) {
            return back()->with('error', __('Invalid database credentials. ' . $validateDatabase));
        }
        $uploadsql = $this->importSql();
        if ($uploadsql !== true) {
            return back()->with('error', __('Unable to upload database, check database logins. ' . $uploadsql));
        }
        $path = base_path('.env');
        if (file_exists($path)) {
            foreach ($request->types as $type) {
                $this->writeEnvironmentFile($type, $request[$type]);
            }
            return redirect()->route('install.setting');
        }else {
            return redirect()->route('install.setting')->withError('Check env details');
        }        
    }

    public function setting()
    {        
        return view('install.setting');
    }

    // finish INstallation

    /**
     * Create Admin user and update Site Settings
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function finishSetup(Request $request)
    {
        //Update site settings
        $setting = Setting::first();
        $setting->title = $request->name;
        $setting->email = $request->email;
        $setting->save();

        // Create admin user
        $user = new User();
        $user->name      = $request->admin_name;
        $user->email     = $request->admin_email;
        $user->password  = Hash::make($request->admin_password);
        $user->user_role = 'admin';
        $user->username = $request->admin_username;
        $user->email_verified_at = date('Y-m-d H:m:s');
        $user->save();       
        
        // save env files
        $this->writeEnvironmentFile('APP_NAME', $request->name);
        $this->writeEnvironmentFile('APP_URL', $request->app_url);

        // Copy necessary files
        $oldsp= base_path('app/Providers/RouteServiceProvider.php');        
        copy( install_files('RouteServiceProvider.php'), $oldsp);        
        $oldkernel= base_path('app/Http/Kernel.php');        
        copy( install_files('Kernel.php'), $oldkernel);
        
        // return $request;       
        return view('install.finish', compact('request'));

    }
    /**
     * Validate the database credentials
     *
     * @return bool|string
     */
    private function validateDatabaseCredentials()
    {
        $settings = config("database.connections.mysql");

        config([
            'database' => [
                'default' => 'mysql',
                'connections' => [
                    'mysql' => array_merge($settings, [
                        'driver' => 'mysql',
                        'host' => request()->input('DB_HOST'),
                        'port' => 3306,
                        'database' => request()->input('DB_DATABASE'),
                        'username' => request()->input('DB_USERNAME'),
                        'password' => request()->input('DB_PASSWORD'),
                    ]),
                ],
            ],
        ]);

        DB::purge();

        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    
    public function writeEnvironmentFile($type, $val) {
        $path = base_path('.env');
        if (file_exists($path)) {
            $val = '"'.trim($val).'"';
            file_put_contents($path, str_replace(
                $type.'="'.env($type).'"', $type.'='.$val, file_get_contents($path)
            ));
        }
    }

    /**
     * Import the Database
     *
     * @return bool|string
     */
    private function importSql() {
        $sql_path = base_path('database.sql');
        try {
            DB::unprepared(file_get_contents($sql_path));
        } catch (\Exception $e) {
            return $e->getMessage();
        }       
        return true;
    }
}
