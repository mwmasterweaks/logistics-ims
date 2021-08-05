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
            $tbl3 = DB::table('stocks')->whereBetween("created_at", [$from->toDateString(), $to->toDateString()])->get();


            if ($request->filterBy == 'deliverySum') {
                $tbl =  $tbl->with('sales_order.client');

                $tbl =  $tbl->whereBetween("delivery_receipts.updated_at", [$from, $to])
                    ->get();
            } else if ($request->filterBy == 'supplierSum') {

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
            $items = [];
            $purchase = [];
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

                $return = DB::table('item_sales_return')
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
            } elseif ($request->filterBy == "items") {

                foreach ($tbl3 as $item) {
                    $total_qty_in = $item->qty_in;
                    $total_qty_out = $item->qty_out;
                    $qty = $total_qty_in - $total_qty_out;
                    $product = DB::table('items')->where('id', $item->item_id)->value('description');

                    $collect2 = collect();
                    $collect2->put('stock_id', $item->item_id);
                    $collect2->put('stock_desc', $product);
                    $collect2->put('stock_qty', $qty);
                    $collect2->put(
                        'item',
                        $item
                    );

                    array_push($items, $collect2);
                }
            } elseif ($request->filterBy == "purchaseSum") {
                if ($request->supplierSelected != null) {
                    $supplierSelected = $request->supplierSelected;
                    $tbl4 = PurchaseOrder::with('user', 'supplier')
                        ->where('supplier_id', $supplierSelected)
                        ->whereBetween('created_at', [$from, $to])->get();
                } else
                    $tbl4 = PurchaseOrder::with('user', 'supplier')->whereBetween('created_at', [$from, $to])->get();
            }
            foreach ($tbl4 as $item) {

                $retSup = $item->supplier;
                $supplier = $retSup->name;
                $retReq = $item->user;
                $req = $retReq->name;
                $date = new Carbon($item->created_at);


                $collect = collect();
                $collect->put('number', $item->id);
                $collect->put('supplier', $supplier);
                $collect->put('requestor', $req);
                $collect->put('total', number_format($item->total));
                $collect->put('date', $date->toFormattedDateString());
                $collect->put('status', $item->status);

                array_push($purchase, $collect);
            }



            $ret = collect();
            $ret->put("totalQty", $total_qty);
            $ret->put("data", $data);
            $ret->put("items", $items);
            $ret->put("purchase", $purchase);
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
            $tbl2 = SalesReturn::with("sales_return_details");
            $tbl3 = DB::table('stocks')->where("item_id", $item)->whereBetween("received_at", [$from->toDateString(), $to->toDateString()])->get();

            // return $tbl3;
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

                $tbl2 = $tbl2
                    ->whereHas("client",  function ($query) use ($c_id) {
                        $query->where("clients.id", $c_id);
                    })
                    ->where("status", "!=", "For Approval")
                    ->whereBetween("updated_at", [$from->toDateString(), $to->toDateString()])
                    ->whereHas("sales_return_details", function ($query) use ($item) {
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

                $tbl2 = $tbl2
                    ->where("status", "!=", "For Approval")
                    ->whereBetween("updated_at", [$from->toDateString(), $to->toDateString()])
                    ->whereHas("sales_return_details", function ($query) use ($item) {
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
            $data2 = [];
            $data3 = [];
            $total_qty = 0;
            $type = "";
            $dateStocks = $dateStocks->toFormattedDateString();

            // for DR & S.O
            foreach ($tbl as $item) {
                $total_delivered_qty = 0;

                $collect = collect();
                $date = new Carbon($item->updated_at);
                $sales_order = $item->sales_order;
                $client = $sales_order->client;
                $type = "Invoice";
                $dateSales =
                    new Carbon($sales_order->created_at);

                $desc = "";
                foreach ($item->delivery_receipt_details as $dr) {

                    $pivot = $dr->pivot;
                    $total_delivered_qty += $pivot->qty;
                    $desc = $dr->description;
                    $dr_qty = $pivot->qty;
                }

                $collect->put('type', $type);
                $collect->put('date', $date->toFormattedDateString());
                $collect->put('dateSales', $dateSales->toFormattedDateString());
                $collect->put('sales_order_id', $item->sales_order_id);
                $collect->put('dr_id', $item->id);
                $collect->put('name', $client->name);
                $collect->put('memo', $item->memo);
                $collect->put('class', $client->class);
                $collect->put('desc', $desc);
                $collect->put('qty', $dr_qty);

                $total_qty += $dr_qty;

                $collect->put('item', $item);
                array_push($data, $collect);
            }

            // for sales  returns
            foreach ($tbl2 as $item2) {

                $collect2 = collect();
                $date_return = new Carbon($item2->date_return);
                $return_id = $item2->id;
                $client = $item2->client;
                $type = "Return";


                $desc = "";
                foreach ($item2->sales_return_details as $sr) {

                    $pivot = $sr->pivot;
                    $sr_qty = $pivot->qty;
                    $desc = $sr->description;
                }

                $collect2->put('type', $type);
                $collect2->put('date', $date_return->toFormattedDateString());
                $collect2->put('return_id', $return_id);
                $collect2->put('name', $client->name);
                $collect2->put('desc', $desc);
                $collect2->put('qty', $sr_qty);

                $total_qty += $sr_qty;

                $collect2->put('item', $item2);
                array_push($data2, $collect2);
            }

            // for purchase orders
            foreach ($tbl3 as $item3) {

                $collect3 = collect();
                $date_receive = new Carbon($item3->received_at);
                $purchase = $item3->purchase_order_id;
                $stock_qty = $item3->qty_in;
                $type = "Item Receive";

                $collect3->put('type', $type);
                $collect3->put('date_receive', $date_receive->toFormattedDateString());
                $collect3->put('purchase', $purchase);
                $collect3->put('stock_qty', $stock_qty);


                $collect3->put('item', $item3);
                array_push($data3, $collect3);
            }


            $ret = collect();
            $ret->put("totalQty", $total_qty);
            $ret->put("dateStocks", $dateStocks);
            $ret->put("stocks", $stocks);
            $ret->put("data", $data);
            $ret->put("return", $data2);
            $ret->put("receive", $data3);
            return response()->json($ret);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }

    public function showAllInventoryReport(Request $request)
    {
        try {

            $item = $request->itemSelected;


            $tbl = DeliveryReceipt::with("delivery_receipt_details");
            $tbl2 = SalesReturn::with("sales_return_details");
            $tbl3 = DB::table('stocks')->where("item_id", $item)->get();

            $tbl = $tbl
                ->where("status", "!=", "for delivery")
                ->whereHas("delivery_receipt_details", function ($query) use ($item) {
                    $query->where("id", $item);
                })
                ->get();

            $tbl2 = $tbl2
                ->whereHas("sales_return_details", function ($query) use ($item) {
                    $query->where("id", $item);
                })
                ->where("status", "!=", "For Approval")
                ->get();



            $stocks = DB::table('stocks')
                ->latest('created_at')
                ->where('item_id', $item)
                ->get();


            foreach ($stocks as $stock) {

                $dateStocks = new Carbon($stock->created_at);
                $stocks = $stock->qty_in;
            }


            $data = [];
            $data2 = [];
            $data3 = [];
            $total_qty = 0;
            $type = "";
            $dateStocks = $dateStocks->toFormattedDateString();

            // for DR & S.O
            foreach ($tbl as $item) {
                $total_delivered_qty = 0;

                $collect = collect();
                $date = new Carbon($item->updated_at);
                $sales_order = $item->sales_order;
                $client = $sales_order->client;
                $type = "Invoice";

                $dateSales =
                    new Carbon($sales_order->created_at);

                $desc = "";
                foreach ($item->delivery_receipt_details as $dr) {

                    $pivot = $dr->pivot;
                    $total_delivered_qty += $pivot->qty;
                    $desc = $dr->description;
                    $dr_qty = $pivot->qty;
                }

                $collect->put('type', $type);
                $collect->put('date', $date->toFormattedDateString());
                $collect->put('dateSales', $dateSales->toFormattedDateString());
                $collect->put('sales_order_id', $item->sales_order_id);
                $collect->put('dr_id', $item->id);
                $collect->put('name', $client->name);
                $collect->put('memo', $item->memo);
                $collect->put('class', $client->class);
                $collect->put('desc', $desc);
                $collect->put('qty', $dr_qty);

                $total_qty += $dr_qty;

                $collect->put('item', $item);
                array_push($data, $collect);
            }
            // for sales  returns
            foreach ($tbl2 as $item2) {

                $collect2 = collect();
                $date_return = new Carbon($item2->date_return);
                $return_id = $item2->id;
                $client = $item2->client;
                $type = "Return";


                $desc = "";
                foreach ($item2->sales_return_details as $sr) {

                    $pivot = $sr->pivot;
                    $sr_qty = $pivot->qty;
                    $desc = $sr->description;
                }

                $collect2->put('type', $type);
                $collect2->put('date', $date_return->toFormattedDateString());
                $collect2->put('return_id', $return_id);
                $collect2->put('name', $client->name);
                $collect2->put('desc', $desc);
                $collect2->put('qty', $sr_qty);

                $total_qty += $sr_qty;

                $collect2->put('item', $item2);
                array_push($data2, $collect2);
            }

            // for purchase order
            foreach ($tbl3 as $item3) {

                $collect3 = collect();
                $date_receive = new Carbon($item3->received_at);
                $purchase = $item3->purchase_order_id;
                $stock_qty = $item3->qty_in;
                $type = "Item Receive";

                $collect3->put('type', $type);
                $collect3->put('date_receive', $date_receive->toFormattedDateString());
                $collect3->put('purchase', $purchase);
                $collect3->put('stock_qty', $stock_qty);


                $collect3->put(
                    'item',
                    $item3
                );
                array_push($data3, $collect3);
            }


            $ret = collect();
            $ret->put("totalQty", $total_qty);
            $ret->put("dateStocks", $dateStocks);
            $ret->put("stocks", $stocks);
            $ret->put("data", $data);
            $ret->put("return", $data2);
            $ret->put("receive", $data3);

            return response()->json($ret);


            // return response()->json($tbl);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }
}
