<?php

namespace App\Http\Controllers;

use App\Models\SausageEntry;
use Faker\Core\Barcode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mockery\Matcher\Type;

class SausageController extends Controller
{
    public function __construct()
    {
        $this->middleware('session_check')->except(['insertBarcodes', 'lastInsert']);
    }

    public function index()
    {
        $title = "Dashboard";

        $total_tonnage = DB::table('sausage_entries')
            ->whereDate('sausage_entries.created_at', today())
            ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
            ->sum(DB::raw('1 * items.qty_per_unit_of_measure'));

        $total_entries =  DB::table('sausage_entries')
            ->whereDate('sausage_entries.created_at', today())
            ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
            ->count('sausage_entries.barcode');

        $highest_product = DB::table('sausage_entries')
            ->whereDate('sausage_entries.created_at', today())
            ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
            ->where('items.code', '!=', null)
            ->select('sausage_entries.barcode', 'items.code', 'items.description', DB::raw('COUNT(sausage_entries.barcode) as total_count'), 'items.qty_per_unit_of_measure')
            ->groupBy('sausage_entries.barcode', 'items.code', 'items.description', 'items.qty_per_unit_of_measure')
            ->orderBy('total_count', 'DESC')
            ->limit(1)
            ->get()->toArray();

        $lowest_product = DB::table('sausage_entries')
            ->whereDate('sausage_entries.created_at', today())
            ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
            ->where('items.code', '!=', null)
            ->select('sausage_entries.barcode', 'items.code', 'items.description', DB::raw('COUNT(sausage_entries.barcode) as total_count'), 'items.qty_per_unit_of_measure')
            ->groupBy('sausage_entries.barcode', 'items.code', 'items.description', 'items.qty_per_unit_of_measure')
            ->orderBy('total_count', 'ASC')
            ->limit(1)
            ->get()->toArray();

        $wrong_entries =  DB::table('sausage_entries')
            ->whereDate('sausage_entries.created_at', today())
            ->where('items.code', null)
            ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
            ->count('sausage_entries.barcode');

        return view('sausage.dashboard', compact('title', 'total_tonnage', 'total_entries', 'highest_product', 'lowest_product', 'wrong_entries'));
    }

    public function productionEntries($filter = null)
    {
        $title = "Todays-Entries";

        if (!$filter) {
            # no filter
            $entries = DB::table('sausage_entries')
                ->whereDate('sausage_entries.created_at', today())
                ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
                ->select('sausage_entries.barcode', 'items.code', 'items.description', DB::raw('COUNT(sausage_entries.barcode) as total_count'), 'items.qty_per_unit_of_measure')
                ->groupBy('sausage_entries.barcode', 'items.code', 'items.description', 'items.qty_per_unit_of_measure')
                ->orderBy('total_count', 'DESC')
                ->get();
        } elseif ($filter == 'highest-product') {
            $entries = DB::table('sausage_entries')
                ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
                ->whereDate('sausage_entries.created_at', today())
                ->where('items.code', '!=', null)
                ->select('sausage_entries.barcode', 'items.code', 'items.description', DB::raw('COUNT(sausage_entries.barcode) as total_count'), 'items.qty_per_unit_of_measure')
                ->groupBy('sausage_entries.barcode', 'items.code', 'items.description', 'items.qty_per_unit_of_measure')
                ->orderBy('total_count', 'DESC')
                ->limit(1)
                ->get();
        } elseif ($filter == 'lowest-product') {
            $entries = DB::table('sausage_entries')
                ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
                ->whereDate('sausage_entries.created_at', today())
                ->where('items.code', '!=', null)
                ->select('sausage_entries.barcode', 'items.code', 'items.description', DB::raw('COUNT(sausage_entries.barcode) as total_count'), 'items.qty_per_unit_of_measure')
                ->groupBy('sausage_entries.barcode', 'items.code', 'items.description', 'items.qty_per_unit_of_measure')
                ->orderBy('total_count', 'ASC')
                ->limit(1)
                ->get();
        } elseif ($filter == 'probable-wrong-entries') {
            $entries = DB::table('sausage_entries')
                ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
                ->whereDate('sausage_entries.created_at', today())
                ->where('items.code', null)
                ->select('sausage_entries.barcode', 'items.code', 'items.description', DB::raw('COUNT(sausage_entries.barcode) as total_count'), 'items.qty_per_unit_of_measure')
                ->groupBy('sausage_entries.barcode', 'items.code', 'items.description', 'items.qty_per_unit_of_measure')
                ->orderBy('total_count', 'DESC')
                ->get();
        }

        return view('sausage.entries', compact('entries', 'title', 'filter'));
    }

    public function itemsList()
    {
        $title = "Items-List";

        $items = Cache::remember('items_list', now()->addMinutes(480), function () {
            return DB::table('items')
                ->get();
        });

        return view('sausage.items', compact('title', 'items'));
    }

    public function lastInsert()
    {
        $last = DB::table('sausage_entries')
            ->whereDate('created_at', today())
            ->select('origin_timestamp', 'barcode')
            ->orderByDesc('id')
            ->limit(1)
            ->get()->toArray();

        $res = '';
        if (!empty($last)) {
            $origin = $last[0]->origin_timestamp;
            $barcode = $last[0]->barcode;
            $res = strstr($origin,  ' ', true) . ' ' . $barcode;
        }
        return response($res);
    }

    public function insertBarcodes(Request $request)
    {
        try {
            //saving...
            foreach ($request->request_data as $el) {
                $el2 = explode(" ", $el);

                $entries = SausageEntry::upsert([
                    [
                        'origin_timestamp' =>  $el2[0] . ' ' . today()->format('Y-m-d'),
                        'barcode' => $el2[1],
                    ],
                ], ['origin_timestamp', 'barcode'], ['occurrences' => DB::raw('occurrences+1')]);
            }

            return response()->json([
                'success' => true,
                'message' => 'action successful'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
