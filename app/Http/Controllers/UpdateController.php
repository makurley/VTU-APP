<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Artisan;
use Session;
use ZipArchive;

class UpdateController extends Controller
{
    //
    public function index(){
        return view('update.index');
    }

    public function saveFile(Request $request){
        $request->validate([
            'update_file' => 'required|mimes:zip',
        ]);

        if ($request->has('update_file')) {
            if (class_exists('ZipArchive')) {
                // Create update directory.
                // $dir = 'updates';
                // if (!is_dir($dir))
                //     mkdir($dir, 0777, true);

                $zfile = $request->file('update_file');
                $zfile->move(base_path(''),'update.zip');

                //Unzip uploaded update file and remove zip file.
                $zip = new ZipArchive;
                $res = $zip->open(base_path('/update.zip'));

                if ($res === true) {
                    $res = $zip->extractTo(base_path());
                    $zip->close();
                    unlink(base_path('/update.zip'));

                    return $this->saveDatabase($request);
                } else {
                    return back()->with('error','Could not open the updates zip file.');
                }

                return redirect()->route('admin.update.finish');
            }
            else {
                return back()->with('error', 'Please enable ZipArchive extension.');
            }
        }
        else {
            return view('update.index');
        }

    }

    public function saveDatabase($request){        
        // upload sql to database
        $path = base_path('/update.sql');
        // return $path;
        if (is_file($path)) {
            try {
                DB::unprepared(file_get_contents($path));
            } catch (\Exception $e) {
                // dd($e);
                unlink(base_path('/update.sql'));
                return back()->with('error','Could not open the updates sql file. Try again');
            }
            unlink(base_path('/update.sql')); 
        }
        return redirect()->route('admin.update.finish');
    }

    
    public function finish(){
        Artisan::call('view:clear');
        Artisan::call('cache:clear');
        return view('update.finish');
    }

}
