<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{
    BillProduct,
    Country,
    Operator
};

class IntlBillController extends Controller
{
    //
    function countries(Request $request){
        $countries = Country::all();
        return view('admin.ibills.country', compact('countries'));
    }

    function countryAction(Request $request){
        $request->validate([
            'name' => 'required|string',
            'currency' => 'string',
            'code' => 'string',
        ]);
        if($request->id == 0){
            $plan = new Country();
            $mesg = "Country created successfully";
            $plan->status = 1;
        }else{
            $plan = Country::findOrFail($request->id);
            $mesg = "Country updated successfully";
        }
        $plan->name = $request->name;
        $plan->code = $request->code;
        $plan->currency = $request->currency;
        $plan->prefix = $request->prefix;
        $plan->save();
        return back()->withSuccess($mesg);
    }
    public function country_status($id, $status){
        $plan = Country::findorFail($id);
        $plan->status = $status;
        $plan->save();
        return redirect()->back()->withSuccess(__('Country Updated Successfully.'));
    }
    public function countryDelete($id){
        $plan = Country::findorFail($id);
        $plan->delete();
        return redirect()->back()->withSuccess(__('Country Deleted Successfully.'));
    }
    function countryOperators($id){
        $countries = Country::all();
        $c = Country::findOrFail($id);
        $operators = Operator::whereCountryId($id)->get();
        $country = $id;
        $title = "All {$c->name} Operators";
        return view('admin.ibills.operator', compact('countries','operators','country','title'));
    }
    function operators(Request $request){
        $countries = Country::all();
        $operators = Operator::all();
        $country = null;
        $title = "All Operators";
        return view('admin.ibills.operator', compact('countries','operators','country','title'));
    }
    function operatorAction(Request $request){
        if($request->id == 0){
            $plan = new Operator();
            $req = $request->all();
            $req['status'] = 1;
            $req['fee'] = $request->fee ?? 1;
            $req['discount'] = $request->discount ?? 99;
            $plan->create($req);
            $mesg = "Operator created successfully";
        }else{
            $plan = Operator::findOrFail($request->id);
            $mesg = "Operator Updated successfully";
            $req = $request->all();
            $req['fee'] = $request->fee ?? 0;
            $req['discount'] = $request->discount ?? 0;
            $plan->update($req);
        }

        return back()->withSuccess($mesg);
    }
    public function operatorStatus($id, $status){
        $plan = Operator::findorFail($id);
        $plan->status = $status;
        $plan->save();
        return redirect()->back()->withSuccess(__('Operator Updated Successfully.'));
    }
    public function operatorDelete($id){
        $plan = Operator::findorFail($id);
        $plan->delete();
        return redirect()->back()->withSuccess(__('Operator Deleted Successfully.'));
    }
    function operatorProducts ($id){
        $operator = Operator::findorFail($id);
        $operators = Operator::all();
        $products = BillProduct::whereOperatorId($id)->get();
        $title = "All {$operator->name} Products";
        $operator = $id;
        return view('admin.ibills.products', compact('products','operators','operator','title'));
    }
    function products (){
        $operators = Operator::all();
        $products = BillProduct::get();
        $title = "All Products";
        $operator = null;
        return view('admin.ibills.products', compact('products','operators','operator','title'));
    }
    function productAction(Request $request){
        if($request->id == 0){
            $plan = new BillProduct();
            $req = $request->all();
            $req['status'] = 1;
            $plan->create($req);
            $mesg = "Product created successfully";
        }else{
            $plan = BillProduct::findOrFail($request->id);
            $mesg = "Product Updated successfully";
            $req = $request->all();
            $plan->update($req);
        }

        return back()->withSuccess($mesg);
    }
    public function productStatus($id, $status){
        $plan = BillProduct::findorFail($id);
        $plan->status = $status;
        $plan->save();
        return redirect()->back()->withSuccess(__('Product Updated Successfully.'));
    }
    public function productDelete($id){
        $plan = BillProduct::findorFail($id);
        $plan->delete();
        return redirect()->back()->withSuccess(__('Product Deleted Successfully.'));
    }

}
