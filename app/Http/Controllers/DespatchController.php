<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DespatchController extends Controller
{
    public function __construct()
    {
        $this->middleware('session_check')->except(['']);
    }

    public function index()
    {
        $title = "dashboard";

        $transfers = DB::table('idt_transfers')
            ->whereDate('idt_transfers.created_at', today())
            ->select(DB::raw('SUM(idt_transfers.receiver_total_pieces) as total_pieces'), DB::raw('SUM(idt_transfers.receiver_total_weight) as total_weight'), DB::raw('SUM(idt_transfers.total_pieces) as issued_pieces'), DB::raw('SUM(idt_transfers.total_weight) as issued_weight'))
            ->get();

        return view('despatch.dashboard', compact('title', 'transfers'));
    }

    public function getIdt(Helpers $helpers)
    {
        $title = "IDT";

        $filter = '';

        $items = Cache::remember('items_list', now()->addHours(10), function () {
            return DB::table('items')
                ->select('code', 'barcode', 'description', 'qty_per_unit_of_measure', 'unit_count_per_crate')
                ->get();
        });

        $transfer_lines = DB::table('idt_transfers')
            ->leftJoin('items', 'idt_transfers.product_code', '=', 'items.code')
            ->leftJoin('users', 'idt_transfers.user_id', '=', 'users.id')
            ->select('idt_transfers.*', 'items.description as product', 'items.qty_per_unit_of_measure', 'items.unit_count_per_crate', 'users.username')
            ->whereDate('idt_transfers.created_at', today())
            ->orderBy('idt_transfers.created_at', 'DESC')
            ->where('received_by', '=', null)
            ->get();

        return view('despatch.idt', compact('title', 'filter', 'transfer_lines', 'items', 'helpers'));
    }

    public function receiveTransfer(Request $request, Helpers $helpers)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'crates_valid' => 'required|boolean',
            'item_id' => 'required'
        ]);

        if ($validator->fails()) {
            # failed validation
            $messages = $validator->errors();
            foreach ($messages->all() as $message) {
                Toastr::error($message, 'Error!');
            }
            return back();
        }

        try {
            // try update
            DB::table('idt_transfers')
                ->where('id', $request->item_id)
                ->update([
                    'chiller_code' => $request->chiller_code,
                    'receiver_total_crates' => $request->total_crates,
                    'receiver_full_crates' => $request->full_crates,
                    'receiver_incomplete_crate_pieces' => $request->incomplete_pieces,
                    'receiver_total_pieces' => $request->pieces,
                    'receiver_total_weight' => $request->weight,
                    'received_by' => $helpers->authenticatedUserId(),
                    'with_variance' => $request->valid_match,
                    'updated_at' => now(),
                ]);

            Toastr::success('IDT Transfer received successfully', 'Success');
            return redirect()
                ->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
    }

    public function idtReport(Helpers $helpers, $filter=null)
    {
        $title = "IDT-Report";

        $transfer_lines = DB::table('idt_transfers')
            ->leftJoin('items', 'idt_transfers.product_code', '=', 'items.code')
            ->leftJoin('users', 'idt_transfers.received_by', '=', 'users.id')
            ->select('idt_transfers.*', 'items.description as product', 'items.qty_per_unit_of_measure', 'items.unit_count_per_crate', 'users.username')
            ->orderBy('idt_transfers.created_at', 'DESC')
            ->when($filter == 'today', function ($q) {
                $q->whereDate('idt_transfers.created_at', today()); // today only
            })
            ->when($filter == 'history', function ($q) {
                $q->whereDate('idt_transfers.created_at', '>=', now()->subDays(7)); // today plus last 7 days
            })
            ->get();

        return view('despatch.idt-report', compact('title', 'filter', 'transfer_lines', 'helpers'));
    }

    public function idtVarianceReport()
    {
        $title = "IDT-Variance Report";

        $variance_lines = DB::table('idt_transfers')
            ->leftJoin('items', 'idt_transfers.product_code', '=', 'items.code')
            ->select('idt_transfers.product_code', DB::raw('SUM(idt_transfers.total_pieces) as issued_pieces'), DB::raw('SUM(idt_transfers.total_weight) as issued_weight'), DB::raw('SUM(idt_transfers.receiver_total_pieces) as received_pieces'), DB::raw('SUM(idt_transfers.receiver_total_weight) as received_weight'), 'items.description as product')
            ->orderBy('idt_transfers.product_code', 'ASC')
            ->whereDate('idt_transfers.created_at', today())
            ->having(DB::raw('COALESCE(SUM(idt_transfers.total_weight), 0)'), '!=', DB::raw('COALESCE(SUM(idt_transfers.receiver_total_weight), 0)'))
            ->groupBy('idt_transfers.product_code', 'items.description')
            ->get();

        return view('despatch.idt-variance', compact('title', 'variance_lines'));
    }

    public function idtStocksPerChiller()
    {
        $title = "IDT-Variance Report";

        $items = DB::table('idt_transfers')
            ->leftJoin('items', 'idt_transfers.product_code', '=', 'items.code')
            ->select('idt_transfers.product_code', 'idt_transfers.location_code', DB::raw('COALESCE(SUM(idt_transfers.receiver_total_pieces), 0) as received_pieces'), DB::raw('COALESCE(SUM(idt_transfers.receiver_total_weight), 0) as received_weight'), 'items.description as product')
            ->orderBy('idt_transfers.product_code', 'ASC')
            ->whereDate('idt_transfers.created_at', today())
            ->groupBy('idt_transfers.product_code', 'items.description', 'idt_transfers.location_code')
            ->get();

        return view('despatch.idt-per-chiller', compact('title', 'items'));
    }
}
