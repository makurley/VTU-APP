<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\{
    Betsite,
    CablePlan, DatacardPlan,
    DataBundle, Decoder, Education, Electricity, Giftcard, Network
};
use Illuminate\Http\Request;

class BillsController extends Controller
{
    //
    public function api_selection(){
      return view('admin.bills.selection') ;
    }
    public function api_setting(){
        return view('admin.bills.setting');
    }

    public function airtime(){
        $networks = Network::whereStatus(1)->get();
        return view('admin.bills.airtime', compact('networks'));
    }

    // Airtime Status
    public function airtime_status($id, $status){
        $network = Network::findorFail($id);
        $network->airtime = $status;
        $network->save();
        return redirect()->back()->withSuccess(__('Network Updated Successfully.'));
    }
    public function update_airtime (Request $request, $id){
        $request->validate([
            'minimum' => 'required|string|min:2'
        ]);
        $network = Network::findorFail($id);
        $network->minimum = $request->minimum;
        $network->pin_discount = $request->pin_discount;
        $network->discount = $request->discount;
        $network->api_pin_discount = $request->api_pin_discount;
        $network->api_discount = $request->api_discount;
        $network->reseller_pin = $request->reseller_pin;
        $network->reseller = $request->reseller;
        $network->vtu = $request->vtu;
        $network->n3tdata = $request->n3tdata;
        $network->glad = $request->glad;
        $network->legit = $request->legit;
        $network->sochi = $request->sochi;
        $network->save();
        return redirect()->back()->withSuccess(__('Network updated Successfully.'));
    }
    // Data
    public function internet_data(){
        $networks = Network::whereStatus(1)->get();
        return view('admin.bills.data.index', compact('networks'));
    }
    public function datasub_status($id, $status){
        $network = Network::findorFail($id);
        $network->data = $status;
        $network->save();
        return redirect()->back()->withSuccess(__('Network Updated Successfully.'));
    }
    function manage_dataplans($id){
        $network = Network::whereStatus(1)->whereId($id)->first();
        $dataplans = DataBundle::whereNetworkId($id)->whereDeleted(0)->get();
        return view('admin.bills.data.plans', compact('network','dataplans'));
    }

    function create_dataplan(Request $request){
        $request->validate([
            'network_id' => 'required',
            'name' => 'required|string',
            'price' => 'required|numeric',
            // 'code' => 'required|string'
        ]);
        $dataplan = new DataBundle();
        $dataplan->name = $request->name;
        $dataplan->network_id = $request->network_id;
        $dataplan->service = $request->service;
        $dataplan->price = $request->price;
        $dataplan->status = 1;
        $dataplan->code = $request->glad;
        $dataplan->vtu = $request->vtu;
        $dataplan->n3tdata = $request->n3tdata;
        $dataplan->reseller = $request->reseller;
        $dataplan->api= $request->api;
        $dataplan->glad = $request->glad;
        $dataplan->legit = $request->legit;
        $dataplan->flutter1 = $request->flutter1;
        $dataplan->flutter2 = $request->flutter2;
        $dataplan->flutter3 = $request->flutter3;
        $dataplan->sochi = $request->sochi;
        $dataplan->sochi_amount = $request->sochi_amount;
        $dataplan->save();

        return redirect()->back()->withSuccess(__('Dataplan Created Successfully.'));
    }
    function edit_dataplan(Request $request, $id){
        $request->validate([
            'network_id' => 'required',
            'name' => 'required|string',
            'price' => 'required|numeric',
            // 'code' => 'required|string'
        ]);
        $dataplan = DataBundle::findorFail($id);
        $dataplan->name = $request->name;
        $dataplan->price = $request->price;
        $dataplan->service = $request->service;
        $dataplan->reseller = $request->reseller;
        $dataplan->api= $request->api;
        $dataplan->code = $request->glad;
        $dataplan->vtu = $request->vtu;
        $dataplan->n3tdata = $request->n3tdata;
        $dataplan->glad = $request->glad;
        $dataplan->legit = $request->legit;
        $dataplan->flutter1 = $request->flutter1;
        $dataplan->flutter2 = $request->flutter2;
        $dataplan->flutter3 = $request->flutter3;
        $dataplan->sochi = $request->sochi;
        $dataplan->sochi_amount = $request->sochi_amount;
        $dataplan->save();

        return redirect()->back()->withSuccess(__('Dataplan Created Successfully.'));
    }
    public function dataplan_status($id, $status){
        $data = DataBundle::findorFail($id);
        $data->status = $status;
        $data->save();
        return redirect()->back()->withSuccess(__('Dataplan Updated Successfully.'));
    }
    // datacard
    public function datacard(){
        $networks = Network::whereStatus(1)->get();
        return view('admin.bills.datacard.index', compact('networks'));
    }
    public function datacard_status($id, $status){
        $network = Network::findorFail($id);
        $network->datacard = $status;
        $network->save();
        return redirect()->back()->withSuccess(__('Network Updated Successfully.'));
    }
    function manage_datacardplans($id){
        $network = Network::whereStatus(1)->whereId($id)->first();
        $dataplans = DatacardPlan::whereNetworkId($id)->whereDeleted(0)->get();
        return view('admin.bills.datacard.plans', compact('network','dataplans'));
    }

    function create_datacardplan(Request $request){
        $request->validate([
            'network_id' => 'required',
            'name' => 'required|string',
            'price' => 'required|numeric',
        ]);
        $dataplan = new DatacardPlan();
        $dataplan->name = $request->name;
        $dataplan->network_id = $request->network_id;
        $dataplan->size = $request->size;
        $dataplan->price = $request->price;
        $dataplan->reseller = $request->reseller;
        $dataplan->api= $request->api;
        $dataplan->status = 1;
        // $dataplan->vtu = $request->vtu;
        $dataplan->n3tdata = $request->n3tdata;
        $dataplan->glad = $request->glad;
        $dataplan->legit = $request->legit;
        $dataplan->code = $request->glad;
        $dataplan->save();

        return redirect()->back()->withSuccess(__('Dataplan Created Successfully.'));
    }
    function edit_datacardplan(Request $request, $id){
        // return $request;
        $request->validate([
            'network_id' => 'required',
            'name' => 'required|string',
            'price' => 'required|numeric',
        ]);
        $dataplan = DatacardPlan::findorFail($id);
        $dataplan->name = $request->name;
        $dataplan->price = $request->price;
        $dataplan->reseller = $request->reseller;
        $dataplan->api= $request->api;
        $dataplan->size = $request->size;
        // $dataplan->vtu = $request->vtu;
        $dataplan->code = $request->glad;
        $dataplan->n3tdata = $request->n3tdata;
        $dataplan->glad = $request->glad;
        $dataplan->legit = $request->legit;
        $dataplan->save();

        return redirect()->back()->withSuccess(__('Dataplan Updated Successfully.'));
    }
    public function datacardplan_status($id, $status){
        $data = DatacardPlan::findorFail($id);
        $data->status = $status;
        $data->save();
        return redirect()->back()->withSuccess(__('Dataplan Updated Successfully.'));
    }
    // cable tv
    function cabletv(){
        $decoders = Decoder::all();
        return view('admin.bills.cabletv.index', compact('decoders'));
    }
    public function cabletv_status($id, $status){
        $decoder = Decoder::findorFail($id);
        $decoder->status = $status;
        $decoder->save();
        return redirect()->back()->withSuccess(__('Decoder Updated Successfully.'));
    }
    function manage_cabletvplans($id){
        $decoder = Decoder::whereStatus(1)->whereId($id)->first();
        $plans = CablePlan::whereDecoderId($id)->whereDeleted(0)->get();
        return view('admin.bills.cabletv.plans', compact('decoder','plans'));
    }
    function create_cabletvplan(Request $request){
        $request->validate([
            'decoder_id' => 'required',
            'name' => 'required|string',
            'price' => 'required|numeric',
            // 'code' => 'required|string'
        ]);
        $plan = new CablePlan();
        $plan->name = $request->name;
        $plan->decoder_id = $request->decoder_id;
        $plan->price = $request->price;
        $plan->api= $request->api;
        $plan->reseller = $request->reseller;
        $plan->status = 1;
        $plan->code = $request->glad;
        $plan->vtu = $request->vtu;
        $plan->n3tdata = $request->n3tdata;
        $plan->glad = $request->glad;
        $plan->legit = $request->legit;
        $plan->flutter = $request->flutter;
        $plan->sochi = $request->sochi;
        $plan->sochi_amount = $request->sochi_amount;
        $plan->save();

        return redirect()->back()->withSuccess(__('TV plan Created Successfully.'));
    }
    function edit_cabletvplan(Request $request, $id){
        $request->validate([
            'decoder_id' => 'required',
            'name' => 'required|string',
            'price' => 'required|numeric',
            // 'code' => 'required|string'
        ]);
        $plan = CablePlan::findorFail($id);
        $plan->name = $request->name;
        $plan->price = $request->price;
        $plan->api= $request->api;
        $plan->reseller = $request->reseller;
        $plan->code = $request->glad;
        $plan->vtu = $request->vtu;
        $plan->n3tdata = $request->n3tdata;
        $plan->legit = $request->legit;
        $plan->glad = $request->glad;
        $plan->flutter = $request->flutter;
        $plan->sochi = $request->sochi;
        $plan->sochi_amount = $request->sochi_amount;
        $plan->save();

        return redirect()->back()->withSuccess(__('Cable plan Created Successfully.'));
    }
    public function cableplan_status($id, $status){
        $plan = CablePlan::findorFail($id);
        $plan->status = $status;
        $plan->save();
        return redirect()->back()->withSuccess(__('Plan Updated Successfully.'));
    }

    // electricity
    public function electricity(){
        $powers = Electricity::whereDeleted(0)->get();
        return view('admin.bills.electricity', compact('powers'));
    }
    public function electricity_status($id, $status){
        $plan = Electricity::findorFail($id);
        $plan->status = $status;
        $plan->save();
        return redirect()->back()->withSuccess(__('Plan Updated Successfully.'));
    }
    function create_electricity(Request $request){
        $request->validate([
            'fee' => 'required',
            'name' => 'required|string',
            'minimum' => 'required|numeric',
            // 'code' => 'required|string'
        ]);
        $plan = new Electricity();
        $plan->name = $request->name;
        $plan->fee = $request->fee;
        $plan->minimum = $request->minimum;
        $plan->code = $request->glad;
        $plan->vtu = $request->vtu;
        $plan->n3tdata = $request->n3tdata;
        $plan->legit = $request->legit;
        $plan->glad = $request->glad;
        $plan->flutter = $request->flutter;
        $plan->flutter2 = $request->flutter2;
        $plan->sochi = $request->sochi;
        $plan->sochi_product = $request->sochi_product;
        $plan->save();

        return redirect()->back()->withSuccess(__('Electricity Created Successfully.'));
    }
    function edit_electricity(Request $request, $id){
        $request->validate([
            'fee' => 'required',
            'name' => 'required|string',
            'minimum' => 'required|numeric',
            'code' => 'string'
        ]);
        $plan = Electricity::findOrFail($id);
        $plan->name = $request->name;
        $plan->fee = $request->fee;
        $plan->minimum = $request->minimum;
        $plan->code = $request->glad;
        $plan->vtu = $request->vtu;
        $plan->flutter = $request->flutter;
        $plan->flutter2 = $request->flutter2;
        $plan->n3tdata = $request->n3tdata;
        $plan->glad = $request->glad;
        $plan->legit = $request->legit;
        $plan->sochi = $request->sochi;
        $plan->sochi_product = $request->sochi_product;
        $plan->save();

        return redirect()->back()->withSuccess(__('Electricity Updated Successfully.'));
    }

    // Bulk sms
    public function bulksms(){
        return view('admin.bills.bulksms');
    }
    // Airtme swap
    public function airtime_swap(){
        $networks = Network::whereStatus(1)->get();
        return view('admin.bills.a2cash', compact('networks'));
    }
    // Airtime swap Status
    public function airtimeswap_status($id, $status){
        $network = Network::findorFail($id);
        $network->swap = $status;
        $network->save();
        return redirect()->back()->withSuccess(__('Network Updated Successfully.'));
    }
    public function update_airtimeswap (Request $request, $id){
        $request->validate([
            'number' => 'required|string|min:2',
            'rate' => 'required'
        ]);
        $network = Network::findorFail($id);
        $network->rate = $request->rate;
        $network->number = $request->number;
        $network->save();
        return redirect()->back()->withSuccess(__('Network updated Successfully.'));
    }
    // Recharge Pins
    public function recharge_pins(){
        $networks = Network::whereStatus(1)->get();
        return view('admin.bills.cards', compact('networks'));
    }
    public function rechargepin_status($id, $status){
        $network = Network::findorFail($id);
        $network->cardpin = $status;
        $network->save();
        return redirect()->back()->withSuccess(__('Network Updated Successfully.'));
    }
    public function update_rechargepin (Request $request, $id){
        $request->validate([
            'p_code' => 'required'
        ]);
        $network = Network::findorFail($id);
        $network->p_code = $request->p_code;
        $network->save();
        return redirect()->back()->withSuccess(__('Network updated Successfully.'));
    }

    // Education
    public function education(){
        $services = Education::whereDeleted(0)->get();
        return view('admin.bills.education', compact('services'));
    }

    public function education_status($id, $status){
        $plan = Education::findorFail($id);
        $plan->status = $status;
        $plan->save();
        return redirect()->back()->withSuccess(__('Plan Updated Successfully.'));
    }
    public function update_education (Request $request, $id){
        $request->validate([
            'glad' => 'required',
            'name' => 'required|string',
            'price' => 'required|numeric'
        ]);
        $plan = Education::findorFail($id);
        $plan->name = $request->name;
        $plan->price = $request->price;
        $plan->reseller = $request->reseller;
        $plan->api = $request->api;
        $plan->code = $request->glad;
        // $plan->vtu = $request->vtu;
        $plan->n3tdata = $request->n3tdata;
        $plan->glad = $request->glad;
        $plan->legit = $request->legit;
        $plan->save();
        return redirect()->back()->withSuccess(__('Plan updated Successfully.'));
    }

    // BEtting
    public function betting(){
        $plans = Betsite::orderBy('name')->get();
        return view('admin.bills.bet', compact('plans'));
    }
    public function bet_status($id, $status){
        $plan = Betsite::findorFail($id);
        $plan->status = $status;
        $plan->save();
        return redirect()->back()->withSuccess(__('Plan Updated Successfully.'));
    }
    function create_bet(Request $request){
        $request->validate([
            'fee' => 'required',
            'name' => 'required|string',
            'minimum' => 'required|numeric',
            // 'code' => 'required|string'
        ]);
        $plan = new Betsite();
        $plan->name = $request->name;
        $plan->fee = $request->fee;
        $plan->minimum = $request->minimum;
        $plan->code = $request->code;
        $plan->sochi_code = $request->sochi_code;
        $plan->sochi_operator = $request->sochi_operator;
        $plan->save();

        return redirect()->back()->withSuccess(__('Betsite Created Successfully.'));
    }
    function edit_bet(Request $request, $id){
        $request->validate([
            'fee' => 'required',
            'name' => 'required|string',
            'minimum' => 'required|numeric',
            'code' => 'string'
        ]);
        $plan = Betsite::findOrFail($id);
        $plan->name = $request->name;
        $plan->fee = $request->fee;
        $plan->minimum = $request->minimum;
        $plan->code = $request->code;
        $plan->sochi_code = $request->sochi_code;
        $plan->sochi_operator = $request->sochi_operator;
        $plan->save();

        return redirect()->back()->withSuccess(__('Betsite Updated Successfully.'));
    }


    public function utility_setting(){
        return view('admin.bills.utility');
    }

    // Giftcard
    public function giftcard(){
        $giftcards = Giftcard::get();
        return view('admin.bills.giftcard', compact('giftcards'));
    }
    public function giftcard_status($id, $status){
        $plan = Giftcard::findorFail($id);
        $plan->status = $status;
        $plan->save();
        return redirect()->back()->withSuccess(__('Giftcard Updated Successfully.'));
    }
    function giftcardAction(Request $request){
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric'
        ]);
        if($request->id == 0){
            $plan = new Giftcard();
            $mesg = "Giftcard created successfully";
            $plan->status = 1;
        }else{
            $plan = Giftcard::findOrFail($request->id);
            $mesg = "Giftcard created successfully";
        }
        $plan->name = $request->name;
        $plan->desc = $request->desc;
        $plan->value = $request->value;
        $plan->amount = $request->amount;
        $plan->operator = $request->operator;
        $plan->product = $request->product;
        $plan->price = $request->price;
        $plan->save();
        return back()->withSuccess($mesg);
    }
}
