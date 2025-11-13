<?php

namespace App\Http\Controllers\Back;

use App\Models\{
    ApiSetting,
    Setting, SystemSetting
};
use Artisan;
use Str;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return view('admin.setup.index');
    }
    public function payment()
    {
        return view('admin.setup.payment');
    }
    public function features()
    {
        return view('admin.setup.features');
    }
    public function custom_styles()
    {
        return view('admin.setup.custom');
    }

    function update(Request $request){
        $input = $request->all();
        if($request->is_announcement != 1) {
            $input['is_announcement'] =0;
        }
        else{
            $input['is_announcement'] = $request->is_announcement;
        }
        if($request->hasFile('favicon')){
            $image = $request->file('favicon');
            $imageName = 'favicon.png';
            $image->move(public_path('uploads'),$imageName);
            $input['favicon'] =$imageName;
        }
        if($request->hasFile('logo')){
            $image = $request->file('logo');
            $imageName = 'logo.png';
            $image->move(public_path('uploads'),$imageName);
            $input['logo'] =$imageName;
        }
        if($request->hasFile('touch_icon')){
            $image = $request->file('touch_icon');
            $imageName = 'touch.png';
            $image->move(public_path('uploads'),$imageName);
            $input['touch_icon'] =$imageName;
        }

        $setting = Setting::first();
        $setting->update($input);

        return redirect()->back()->with('success',__('Settings Updated Successfully.'));
    }
    public function envkeyUpdate(Request $request)
    {
        foreach ($request->types as $key => $type) {
            $this->overWriteEnvFile($type, $request[$type]);
        }
        return back()->withSuccess("Settings updated successfully");

    }

    function systemUpdate(Request $request)
    {
        $setting = SystemSetting::where('name', $request->name)->first();
        if($setting !=null){
            $setting->value = $request->value;
            $setting->save();
        }
        else{
            $setting = new SystemSetting;
            $setting->name = $request->name;
            $setting->value = $request->value;
            $setting->save();
        }

        return '1';
    }

    public function api_settings(Request $request)
    {
        // return $request;
        foreach ($request->types as $key => $type) {
            if($type == 'site_name'){
                $this->overWriteEnvFile('APP_NAME', $request[$type]);
            }
            else {
                $sys_settings = ApiSetting::where('name', $type)->first();

                if($sys_settings!=null){
                    if(gettype($request[$type]) == 'array'){
                        $sys_settings->value = json_encode($request[$type]);
                    }
                    else {
                        $sys_settings->value = $request[$type];
                    }
                    $sys_settings->save();
                }
                else{
                    $sys_settings = new ApiSetting;
                    $sys_settings->name = $type;
                    if(gettype($request[$type]) == 'array'){
                        $sys_settings->value = json_encode($request[$type]);
                    }
                    else {
                        $sys_settings->value = $request[$type];
                    }
                    $sys_settings->save();
                }
            }
        }

        Artisan::call('cache:clear');

        return redirect()->back()->withSuccess(__('Settings Updated Successfully.'));
    }
    public function store_settings(Request $request)
    {
        // return $request;
        foreach ($request->types as $key => $type) {
            if($type == 'site_name'){
                $this->overWriteEnvFile('APP_NAME', $request[$type]);
            }
            else {
                $sys_settings = SystemSetting::where('name', $type)->first();

                if($sys_settings!=null){
                    if(gettype($request[$type]) == 'array'){
                        $sys_settings->value = json_encode($request[$type]);
                    }
                    else {
                        $sys_settings->value = $request[$type];
                    }
                    $sys_settings->save();
                }
                else{
                    $sys_settings = new SystemSetting;
                    $sys_settings->name = $type;
                    if(gettype($request[$type]) == 'array'){
                        $sys_settings->value = json_encode($request[$type]);
                    }
                    else {
                        $sys_settings->value = $request[$type];
                    }
                    $sys_settings->save();
                }
            }
        }

        Artisan::call('cache:clear');

        return redirect()->back()->withSuccess(__('Settings Updated Successfully.'));
    }
    public function overWriteEnvFile($type, $val)
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            $val = '"'.trim($val).'"';
            if(is_numeric(strpos(file_get_contents($path), $type)) && strpos(file_get_contents($path), $type) >= 0){
                file_put_contents($path, str_replace(
                    $type.'="'.env($type).'"', $type.'='.$val, file_get_contents($path)
                ));
            }
            else{
                file_put_contents($path, file_get_contents($path)."\r\n".$type.'='.$val);
            }
        }
    }
}
