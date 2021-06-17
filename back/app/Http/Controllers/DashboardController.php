<?php

namespace App\Http\Controllers;

use App\Item;
use App\Type;
use App\Category;
use App\Warehouse;
use App\User;
use App\SalesOrder;
use App\DeliveryReceipt;
use App\PurchaseOrder;
use App\SalesReturn;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use PhpParser\Node\Stmt\Else_;

class DashboardController extends Controller
{

    public function showItemInventoryReport(Request $request)
    {
        try {
            if ($request->end == null)
                $request->end = $request->from;

            $from = new Carbon($request->from);
            $to = new Carbon($request->to);
            $to = $to->addDay();

            $tbl = DeliveryReceipt::with("delivery_receipt_details");

            if ($request->filterBy == 'clientSum') {
                $c_id = $request->client_id;
                $tbl =  $tbl->whereHas("sales_order.client",  function ($query) use ($c_id) {
                    $query->where("clients.id", $c_id);
                })
                    ->get();
            } else if ($request->filterBy == 'itemSum') {
                $tbl =  $tbl->with('sales_order.client');

                $tbl =  $tbl->whereBetween("delivery_receipts.updated_at", [$from, $to])
                    ->get();
            } else if ($request->filterBy == 'deliverySum') {
                $tbl =  $tbl->with('sales_order.client');

                $tbl =  $tbl->whereBetween("delivery_receipts.updated_at", [$from, $to])
                    ->get();
            }




            // Supplier summary

            else if ($request->filterBy == 'supplierSum') {

                $s_id = $request->supplierSelected;
                $tbl = PurchaseOrder::whereHas("supplier", function ($query) use ($s_id) {
                    $query->where("suppliers.id", $s_id);
                })
                    ->whereBetween('created_at', [$from, $to])
                    ->where("status", "!=", "draft")
                    ->get();

                return response()->json($tbl);
            }


            // return for delivery/item/client summary
            $data = [];
            $total_qty = 0;
            if ($request->filterBy == "deliverySum")
                foreach ($tbl as $item) {
                    $total_delivered_qty = 0;

                    $collect = collect();
                    $date = new Carbon($item->updated_at);
                    $sales_order = $item->sales_order;
                    $client = $sales_order->client;

                    $desc = "";
                    foreach ($item->delivery_receipt_details as $dr) {

                        $pivot = $dr->pivot;
                        $total_delivered_qty += $pivot->qty;
                        $desc = $dr->description;
                    }


                    $collect->put('date', $date->toFormattedDateString());
                    $collect->put('sales_order_id', $item->sales_order_id);
                    $collect->put('dr_id', $item->id);
                    $collect->put('name', $client->name);
                    $collect->put('memo', $item->memo);
                    $collect->put('class', $client->class);
                    $collect->put('desc', $desc);

                    $collect->put('qty', $total_delivered_qty);

                    $total_qty += $total_delivered_qty;

                    $collect->put('item', $item);
                    array_push($data, $collect);
                }
            elseif ($request->filterBy == "salesReturn") {

                $return = DB::table('sales_return_item')
                    ->whereBetween('date_return', [$from, $to])
                    ->get();

                foreach ($return as $item) {
                    $collect = collect();
                    $collect->put('id', $item->item_id);
                    $collect->put('serial', $item->serial);
                    $collect->put('qty', $item->qty);
                    $collect->put('date', $item->date_return);
                    $collect->put('status', $item->status);
                    $total_qty += $item->qty;
                    array_push($data, $collect);
                }
            }



            $ret = collect();
            $ret->put("totalQty", $total_qty);
            $ret->put("data", $data);
            return response()->json($ret);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }

    public function showClientInventoryReport(Request $request)
    {
        try {

            $dateselected = (object) $request->dateSelected;
            $from = new Carbon($dateselected->from);
            $to = new Carbon($dateselected->to);
            $to = $to->addDay();
            $c_id = $request->clientSelected;
            $item = $request->itemSelected;



            $tbl = DeliveryReceipt::with("delivery_receipt_details");

            if ($c_id != null) {
                $tbl = $tbl
                    ->whereHas("sales_order.client",  function ($query) use ($c_id) {
                        $query->where("clients.id", $c_id);
                    })
                    ->where("status", "!=", "for delivery")
                    ->whereBetween("updated_at", [$from->toDateString(), $to->toDateString()])
                    ->whereHas("delivery_receipt_details", function ($query) use ($item) {
                        $query->where("id", $item);
                    })
                    ->get();
            } else {
                $tbl = $tbl
                    ->where("status", "!=", "for delivery")
                    ->whereBetween("updated_at", [$from->toDateString(), $to->toDateString()])
                    ->whereHas("delivery_receipt_details", function ($query) use ($item) {
                        $query->where("id", $item);
                    })
                    ->get();
            }


            $stocks = DB::table('stocks')
                ->latest('created_at')
                ->where('item_id', $item)
                ->get();

            foreach ($stocks as $stock) {

                $dateStocks = new Carbon($stock->created_at);
                $stocks = $stock->qty_in;
            }


            $data = [];
            $total_qty = 0;
            $dateStocks = $dateStocks->toFormattedDateString();


            foreach ($tbl as $item) {
                $total_delivered_qty = 0;

                $collect = collect();
                $date = new Carbon($item->updated_at);
                $sales_order = $item->sales_order;
                $client = $sales_order->client;
                $dateSales =
                    new Carbon($sales_order->created_at);

                $desc = "";
                foreach ($item->delivery_receipt_details as $dr) {

                    $pivot = $dr->pivot;
                    $total_delivered_qty += $pivot->qty;
                    $desc = $dr->description;
                    $dr_qty = $pivot->qty;
                }


                $collect->put('date', $date->toFormattedDateString());
                $collect->put('dateSales', $dateSales->toFormattedDateString());
                // $collect->put('dateStocks', $dateStocks->toFormattedDateString());
                $collect->put('sales_order_id', $item->sales_order_id);
                $collect->put('dr_id', $item->id);
                $collect->put('name', $client->name);
                $collect->put('memo', $item->memo);
                $collect->put('class', $client->class);
                $collect->put('desc', $desc);
                // $collect->put('stocks', $stocks);
                $collect->put('qty', $dr_qty);

                // $total_qty += $total_delivered_qty;
                $total_qty += $dr_qty;

                $collect->put('item', $item);
                array_push($data, $collect);
            }

            $ret = collect();
            $ret->put("totalQty", $total_qty);
            $ret->put("dateStocks", $dateStocks);
            $ret->put("stocks", $stocks);
            $ret->put("data", $data);
            // $ret->put("dateStocks", $dateStocks);
            // $ret->put("stocks", $stocks);
            return response()->json($ret);


            // return response()->json($tbl);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }
}
