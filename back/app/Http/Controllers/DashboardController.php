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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use PhpParser\Node\Stmt\Else_;

class DashboardController extends Controller
{

  public function showItemInventoryReport(Request $request){
 try {
            if ($request->end == null)
                $request->end = $request->start;

            $from = new Carbon($request->start);
            $to = new Carbon($request->end);
            $to = $to->addDay();

            $tbl = DeliveryReceipt::with("delivery_receipt_details");

            if($request->filterBy=='clientSum')
            {
                $c_id = $request->client_id;
                $tbl =  $tbl->whereHas("sales_order.client",  function ($query) use ($c_id) {
                    $query->where("clients.id", $c_id);
                })
                ->get();

            }
            else if ($request->filterBy=='itemSum'){
                $tbl =  $tbl->with('sales_order.client');

            $tbl =  $tbl->whereBetween("delivery_receipts.updated_at", [$from, $to])
             ->get();
            }
            else if ($request->filterBy=='deliverySum') {
                $tbl =  $tbl->with('sales_order.client');

            $tbl =  $tbl->whereBetween("delivery_receipts.updated_at", [$from, $to])
             ->get();


            //  for Sales Summary
            }  else if ($request->filterBy=='salesSum'){
                  $tbl = SalesOrder::with(['client'])->whereBetween('sales_orders.updated_at', [$from, $to])
                  ->where("status","!=","approval")
                  ->get();


            $data = [];
            $total_qty = 0;
            foreach ($tbl as $item) {
            $total_ordered_qty = 0;

            $collect = collect();
                $date = new Carbon($item->updated_at);
                $sales_order = $item->sales_order;
                $client = $item->client;
                $num="";
                $status="";

                $desc = "";

                foreach ($item->sales_order_details as $sr) {

                    $pivot = $sr->pivot;
                    $total_ordered_qty += $pivot->qty;
                    $desc = $sr->description;
                    $num = $sr->id;
                    $status = $item->status;
                }

                $collect->put('date',$date->toFormattedDateString());
                $collect->put('sales_order_id',$num);
                $collect->put('name',$client->name);
                $collect->put('desc',$desc);

                // $collect->put('status',$status);

                $collect->put('qty',$total_ordered_qty);

                $total_qty += $total_ordered_qty;
                $collect->put('item',$item);
                array_push($data,$collect);

            }

             $ret = collect();
             $ret->put("totalQty", $total_qty);
             $ret->put("data", $data);
            return response()->json($ret);
            }


// Supplier summary

             else if ($request->filterBy=='supplierSum'){

                  $s_id = $request->supplierSelected;
                  $tbl = PurchaseOrder::whereHas("supplier",function ($query) use ($s_id) {
                        $query->where("suppliers.id", $s_id);
                })
                  ->whereBetween('created_at', [$from, $to])
                  ->where("status","!=","draft")
                  ->get();


                    // $data = [];
                    // foreach ($tbl as $item) {

                    // $collect = collect();
                    //     $date = new Carbon($item->updated_at);

                    //     $collect->put('date',$date->toFormattedDateString());
                    //     $collect->put('purchase_order_id',$item->id);
                    //     $collect->put('status',$item->status);


                    //     // $collect->put('status',$status);


                    //     $collect->put('item',$item);
                    //     array_push($data,$collect);

                    // }

                    // $ret = collect();
                    // $ret->put("data", $data);
                    return response()->json($tbl);
                    }


            // return for delivery/item/client summary
            $data = [];
            $total_qty = 0;
            if ($request->filterBy!="salesSum")
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

                $collect->put('date',$date->toFormattedDateString());
                $collect->put('sales_order_id',$item->sales_order_id);
                $collect->put('dr_id',$item->id);
                $collect->put('name',$client->name);
                $collect->put('desc',$desc);

                $collect->put('qty',$total_delivered_qty);

                $total_qty += $total_delivered_qty;
                $collect->put('item',$item);
                array_push($data,$collect);

            }


             $ret = collect();
             $ret->put("totalQty", $total_qty);
             $ret->put("data", $data);
            return response()->json($ret);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }


}
