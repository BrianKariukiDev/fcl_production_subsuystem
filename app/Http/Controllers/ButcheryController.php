<?php

namespace App\Http\Controllers;

use App\Models\BeheadingData;
use App\Models\ButcheryData;
use App\Models\Helpers;
use App\Models\Product;
use App\Models\Sale;
use App\Models\DebonedData;
use App\Models\SlaughterData;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ButcheryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Helpers $helpers)
    {
        $title = "dashboard";

        $baconers = BeheadingData::whereDate('created_at', Carbon::today())
            ->where('item_code', "G1030")
            ->sum('no_of_carcass');

        $sows = BeheadingData::whereDate('created_at', Carbon::today())
            ->where('item_code', "G1031")
            ->sum('no_of_carcass');

        $baconers_weight = BeheadingData::whereDate('created_at', Carbon::today())
            ->where('item_code', "G1030")
            ->sum('net_weight');

        $sows_weight = BeheadingData::whereDate('created_at', Carbon::today())
            ->where('item_code', "G1031")
            ->sum('net_weight');

        $butchery_date = $helpers->getButcheryDate();

        $lined_baconers = SlaughterData::where('item_code', 'G0110')
            ->whereDate('created_at', $butchery_date)
            ->count();

        $lined_sows = SlaughterData::where('item_code', 'G0111')
            ->whereDate('created_at', $butchery_date)
            ->count();

        $three_parts_baconers = ButcheryData::where('carcass_type', 'G1030')
            ->whereDate('created_at', Carbon::today())
            ->sum('net_weight');

        $three_parts_sows = ButcheryData::where('carcass_type', 'G1031')
            ->whereDate('created_at', Carbon::today())
            ->sum('net_weight');

        $b_legs = ButcheryData::where('carcass_type', 'G1030')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', 'G1100')
            ->sum('net_weight');

        $b_shoulders = ButcheryData::where('carcass_type', 'G1030')
        ->whereDate('created_at', Carbon::today())
        ->where('item_code', 'G1101')
        ->sum('net_weight');

        $b_middles = ButcheryData::where('carcass_type', 'G1030')
        ->whereDate('created_at', Carbon::today())
        ->where('item_code', 'G1102')
        ->sum('net_weight');

        return view('butchery.dashboard', compact('title', 'baconers', 'sows', 'baconers_weight', 'sows_weight', 'lined_baconers', 'lined_sows', 'three_parts_baconers', 'three_parts_sows', 'butchery_date', 'helpers', 'b_legs', 'b_shoulders', 'b_middles'));
    }

    public function scaleOneAndTwo(Helpers $helpers)
    {
        $title = "Scale-1&2";

        $configs = DB::table('scale_configs')
            ->where('section', 'butchery')
            ->select('scale', 'tareweight', 'comport')
            ->get()->toArray();

        $products = DB::table('products')
            ->orWhere('code', 'G1100')
            ->orWhere('code', 'G1101')
            ->orWhere('code', 'G1102')
            ->orderBy('code', 'ASC')
            ->get();

        $beheading_data = DB::table('beheading_data')
            ->whereDate('beheading_data.created_at', Carbon::today())
            ->leftJoin('products', 'beheading_data.item_code', '=', 'products.code')
            ->select('beheading_data.*', 'products.description')
            ->get();

        $butchery_data = DB::table('butchery_data')
            ->whereDate('butchery_data.created_at', Carbon::today())
            ->leftJoin('products', 'butchery_data.item_code', '=', 'products.code')
            ->select('butchery_data.*', 'products.description')
            ->get();
        
        $product_types = DB::table('product_types')
            ->get();

        return view('butchery.scale1-2', compact('title', 'configs', 'products', 'beheading_data', 'butchery_data', 'helpers', 'product_types'));
    }

    public function saveScaleOneData(Request $request)
    {
        try {
            // insert sales substr($string, 0, -1);
            if ($request->carcass_type == "G1032" || $request->carcass_type == "G1033") {
                $new = Sale::create([
                    'item_code' => $request->carcass_type,
                    'no_of_carcass' => $request->no_of_carcass,
                    'net_weight' => $request->net,
                    'process_code' => 0, //process behead pig by default
                    'user_id' => Auth::id(),
                ]);

                Toastr::success('sale recorded successfully','Success');
                return redirect()->back();
            }
            // insert beheading data
            $process_code = 0; //Behead Pig
            if ($request->carcass_type == 'G1031') {
                $process_code = 1; //Behead sow
            }

            $new = BeheadingData::create([
                'item_code' => $request->carcass_type,
                'no_of_carcass' => $request->no_of_carcass,
                'net_weight' => $request->net,
                'process_code' => $process_code,
                'user_id' => Auth::id(),
            ]);

            Toastr::success('record inserted successfully','Success');
            return redirect()->back();

        } catch (\Exception $e) {
            Toastr::error($e->getMessage(),'Error!');
            return back()
                ->withInput();
        }

    }

    public function saveScaleTwoData(Request $request)
    {
        try {
            # insert record
            $process_code = 2; //Breaking Pig, (Leg, Mdl, Shld)
            if ($request->carcass_type == 'G1031') {
                $process_code = 3; //Breaking Sow into Leg,Mid,&Shd

            }
            $new = ButcheryData::create([
                'carcass_type' =>  $request->carcass_type,
                'item_code' =>  $request->item_code,
                'net_weight' => $request->net2,
                'no_of_items' => $request->no_of_items,
                'process_code' => $process_code,
                'product_type' => $request->product_type,
                'user_id' => Auth::id(),
            ]);

            Toastr::success('record inserted successfully','Success');
            return redirect()->back();

        } catch (\Exception $e) {
            Toastr::error($e->getMessage(),'Error!');
            return back()
                ->withInput();
        }

    }

    public function updateScaleTwoData(Request $request)
    {
        try {
            //update
            DB::table('butchery_data')
                ->where('id', $request->item_id)
                ->update([
                    'item_code' => $request->editproduct,
                    'updated_at' => Carbon::now(),
                    ]);


            Toastr::success("record {$request->editproduct} updated successfully",'Success');
            return redirect()->back();

        } catch (\Exception $e) {
            Toastr::error($e->getMessage(),'Error!');
            return back()
                ->withInput();
        }
    }

    public function loadSlaughterDataAjax(Request $request, Helpers $helpers)
    {
        $baconers = DB::table('slaughter_data')
            ->whereDate('created_at', $helpers->getButcheryDate())
            ->where('item_code', 'G0110')
            ->count();

        $sows = DB::table('slaughter_data')
            ->whereDate('created_at', Carbon::parse($request->date))
            ->where('item_code', 'G0111')
            ->count();

        $data = array('baconers'=>$baconers, 'sows'=>$sows);

        return response()->json($data);

    }

    public function scaleThree(Helpers $helpers)
    {
        $title = "Scale-3";

        $configs = DB::table('scale_configs')
            ->where('section', 'butchery')
            ->where('scale', 'scale 3')
            ->select('scale', 'tareweight', 'comport')
            ->get()->toArray();

        $products = DB::table('products')
            ->get();

        $deboning_data = DB::table('deboned_data')
            ->get();

        return view('butchery.scale3', compact('title', 'products', 'configs', 'deboning_data', 'helpers'));
    }

    public function saveScaleThreeData(Request $request)
    {
        try {
            # insert record
            $new = DebonedData::create([
                'item_code' =>  $request->product,
                'net_weight' => $request->net,
                'user_id' => Auth::id(),
            ]);

            Toastr::success("record {$request->product} inserted successfully",'Success');
            return redirect()->back();

        } catch (\Exception $e) {
            Toastr::error($e->getMessage(),'Error!');
            return back()
                ->withInput();
        }

    }

    public function products()
    {
        $title = "products";

        $products = DB::table('products')
            ->where('code', '!=', '')
            ->get();

        return view('butchery.products', compact('title', 'products'));
    }

    public function addProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|unique:products,code',
        ]);

        if ($validator->fails()) {
            # failed validation
            $messages = $validator->errors();
            foreach ($messages->all() as $message) {
                Toastr::error($message, 'Error!');
            }
            return back()
                ->withInput()
                ->with('input_errors', 'add_product')
                ->withErrors($validator);
        }

        $product = Product::create([
            'code' => $request->code,
            'description' => $request->product,
            'product_type' => $request->product_type,
            'input_type' => $request->input_type,
            'often' => $request->often,
            'user_id' => Auth::id(),

        ]);

        Toastr::success("product {$request->product} inserted successfully",'Success');
        return redirect()->back();
    }

    public function scaleSettings(Helpers $helpers)
    {
        $title = "Scale";

        $scale_settings = DB::table('scale_configs')
            ->where('section', 'butchery')
            ->get();

        return view('butchery.scale_settings', compact('title', 'scale_settings', 'helpers'));
    }

    public function changePassword()
    {
        $title = "password";
        return view('butchery.change_password', compact('title'));
    }

    public function getBeheadingReport(Helpers $helpers)
    {
        $title = "Beheading-Report";
        $beheading_data = DB::table('beheading_data')
            ->leftJoin('products', 'beheading_data.item_code', '=', 'products.code')
            ->select('beheading_data.*', 'products.description')
            ->get();

        return view('butchery.beheading', compact('title', 'beheading_data', 'helpers'));

    }

    public function getBrakingReport(Helpers $helpers)
    {
        $title = "Braking-Report";
        $butchery_data = DB::table('butchery_data')
            ->leftJoin('products', 'butchery_data.item_code', '=', 'products.code')
            ->select('butchery_data.*', 'products.description')
            ->get();

        return view('butchery.breaking', compact('title', 'butchery_data', 'helpers'));

    }

    public function getSlicingReport(Helpers $helpers)
    {
        $title = "Deboning-Report";
        $deboning_data = DB::table('deboned_data')
            ->get();

        return view('butchery.deboned', compact('title', 'deboning_data', 'helpers'));

    }

    public function getSalesReport(Helpers $helpers)
    {
        $title = "Sales-Report";
        $sales_data = DB::table('sales')
            ->leftJoin('products', 'sales.item_code', '=', 'products.code')
            ->leftJoin('processes', 'sales.process_code', '=', 'processes.process_code')
            ->select('sales.*', 'products.description', 'processes.process')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('butchery.sales', compact('title', 'sales_data', 'helpers'));

    }
}
