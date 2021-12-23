<?php

namespace App\Http\Controllers;

use App\Stock;
use App\Type;
use App\Category;
use App\Warehouse;
use App\User;
use App\SalesOrder;
use App\DeliveryReceipt;
use App\Item;
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

            // return $request;
            // if (empty($request->from) && empty($request->to)) {
            //     return "empty";
            // }elseif (!empty($request->from)) {

            // }

            $filter = $request->filterBy;
            if ($request->end == null) {
                $request->end = $request->from;
            }

            $from = new Carbon($request->from);
            $to = new Carbon($request->to);

            $temp_purchase = [];
            $temp_delivery = [];
            $temp_return = [];
            $temp_items = [];

            // purchase summary
            if ($filter == 'purchaseSum') {
                if (empty($request->from) && empty($request->to)) {
                    if (!empty($request->supplierSelected)) {

                        $tbl = PurchaseOrder::with(['user', 'supplier'])
                            ->where('supplier_id', $request->supplierSelected)
                            ->get();
                    } else
                        $tbl = PurchaseOrder::with('user', 'supplier')->all();
                } elseif (!empty($request->from)) {
                    if (!empty($request->supplierSelected)) {

                        $tbl = PurchaseOrder::with(['user', 'supplier'])
                            ->where('supplier_id', $request->supplierSelected)
                            ->whereBetween('created_at', [$from, $to])->get();
                    } else
                        $tbl = PurchaseOrder::with('user', 'supplier')->whereBetween('created_at', [$from, $to])->get();
                }



                foreach ($tbl as $item) {
                    $collection = collect($item);
                    array_push($temp_purchase, $collection->all());
                }

                // delivery receipts summary
            } elseif ($filter == 'deliverySum') {
                if (empty($request->from) && empty($request->to)) {
                    $tbl = DeliveryReceipt::with(['delivery_receipt_details', 'sales_order.client'])
                        ->get();
                } elseif (!empty($request->from)) {
                    $tbl = DeliveryReceipt::with(['delivery_receipt_details', 'sales_order.client'])
                        ->whereBetween("delivery_receipts.created_at", [$from, $to])
                        ->get();
                }

                foreach ($tbl as $item) {
                    $collection = collect($item);
                    array_push($temp_delivery, $collection->all());
                }

                // sales return summary
            } elseif ($filter == 'salesReturn') {
                if (empty($request->from) && empty($request->to)) {
                    $tbl = DB::table('item_sales_return')->get();
                } elseif (!empty($request->from)) {
                    $tbl = DB::table('item_sales_return')
                        ->whereBetween('date_return', [$from, $to])->get();
                }

                foreach ($tbl as $item) {
                    $item_desc = Item::select('description')->where('id', $item->item_id)->first();
                    $collection = collect($item);
                    $collection->put('desc', $item_desc);

                    array_push($temp_return, $collection->all());
                }

                // items summary
            } elseif ($filter == 'items') {


                if (!empty($request->itemSelected)) {
                    $tbl = Stock::with(['item'])
                        ->where('item_id', $request->itemSelected)
                        ->join('items', 'stocks.item_id', 'items.id')
                        ->get();
                } else {
                    $tbl = Stock::with(['item'])->join('items', 'stocks.item_id', 'items.id')->orderBy('items.name')->get();
                }
                foreach ($tbl as $item) {
                    $total_qty_in = 0;

                    $total_deduct = DeliveryReceipt::with(['sales_order', 'delivery_receipt_details'])
                        ->join('delivery_receipt_item', 'delivery_receipts.id', 'delivery_receipt_item.delivery_receipt_id')
                        ->where('item_id', $item->item_id)
                        ->whereBetween("created_at", [$from, $to])
                        ->selectRaw('*,sum(delivery_receipt_item.qty)as total_qty ')
                        ->value('total_qty');


                    $total_qty_in = DB::table('stocks')->where('item_id', $item->item_id)->where('created_at', "<=", $to)->sum('qty_in');
                    $qty = $total_qty_in - $total_deduct;
                    $name = DB::table('items')->where('id', $item->item_id)->value('name');
                    $desc = DB::table('items')->where('id', $item->item_id)->value('description');

                    $collect2 = collect();
                    $collect2->put('stock_id', $item->item_id);
                    $collect2->put('stock_desc', $name . ' - ' . $desc);
                    $collect2->put('stock_qty', $qty);
                    $collect2->put('deduct_qty', $total_deduct);
                    $collect2->put(
                        'item',
                        $item
                    );
                    array_push($temp_items, $collect2);
                }
            }

            $data = collect();
            $data->put('purchase', $temp_purchase);
            $data->put('delivery', $temp_delivery);
            $data->put('returns', $temp_return);
            $data->put('items', $temp_items);

            return response()->json($data);
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
            $c_id = $request->clientSelected;
            $item = $request->itemSelected;

            if (empty($c_id)) {
                $tbl = DeliveryReceipt::with(['sales_order', 'delivery_receipt_details'])
                    ->join('delivery_receipt_item', 'delivery_receipts.id', 'delivery_receipt_item.delivery_receipt_id')
                    ->where('item_id', $item)
                    ->selectRaw('*,sum(delivery_receipt_item.qty)as total_qty ')
                    ->whereBetween("created_at", [$from->toDateString(), $to->toDateString()])
                    ->groupBy('id')
                    ->get();
                $tbl2 = SalesReturn::with("sales_return_details")
                    ->where("status", "!=", "For Approval")
                    ->whereBetween("updated_at", [$from->toDateString(), $to->toDateString()])
                    ->whereHas("sales_return_details", function ($query) use ($item) {
                        $query->where("id", $item);
                    })
                    ->get();
            } else {
                $tbl = DeliveryReceipt::with(['sales_order', 'delivery_receipt_details'])
                    ->join('delivery_receipt_item', 'delivery_receipts.id', 'delivery_receipt_item.delivery_receipt_id')
                    ->whereHas("sales_order.client",  function ($query) use ($c_id) {
                        $query->where("clients.id", $c_id);
                    })
                    ->where('item_id', $item)
                    ->selectRaw('*,sum(delivery_receipt_item.qty)as total_qty ')
                    ->whereBetween("created_at", [$from->toDateString(), $to->toDateString()])
                    ->groupBy('id')
                    ->get();

                $tbl2 = SalesReturn::with("sales_return_details")
                    ->whereHas("client",  function ($query) use ($c_id) {
                        $query->where("clients.id", $c_id);
                    })
                    ->where("status", "!=", "For Approval")
                    ->whereBetween("updated_at", [$from->toDateString(), $to->toDateString()])
                    ->whereHas("sales_return_details", function ($query) use ($item) {
                        $query->where("id", $item);
                    })
                    ->get();
            }

            $tbl3 = DB::table('stocks')->where("item_id", $item)->whereBetween("received_at", [$from->toDateString(), $to->toDateString()])->get();



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
            // foreach ($tbl as $item) {
            //     $total_delivered_qty = 0;

            //     $collect = collect();
            //     $date = new Carbon($item->updated_at);
            //     $sales_order = $item->sales_order;
            //     $client = $sales_order->client;
            //     $type = "Invoice";
            //     $dateSales =
            //         new Carbon($sales_order->created_at);

            //     $desc = "";
            //     foreach ($item->delivery_receipt_details as $dr) {

            //         $pivot = $dr->pivot;
            //         $total_delivered_qty += $pivot->qty;
            //         $desc = $dr->description;
            //         $dr_qty = $pivot->qty;
            //     }

            //     $collect->put('type', $type);
            //     $collect->put('date', $date->toFormattedDateString());
            //     $collect->put('dateSales', $dateSales->toFormattedDateString());
            //     $collect->put('sales_order_id', $item->sales_order_id);
            //     $collect->put('dr_id', $item->id);
            //     $collect->put('name', $client->name);
            //     $collect->put('memo', $item->memo);
            //     $collect->put('class', $client->class);
            //     $collect->put('desc', $desc);
            //     $collect->put('qty', $dr_qty);

            //     $total_qty += $dr_qty;

            //     $collect->put('item', $item);
            //     array_push($data, $collect);
            // }

            foreach ($tbl as $item) {
                $total_delivered_qty = 0;

                $collect = collect();
                $client = (object)$item->sales_order->client;
                $request = (object)$item->sales_order;
                $dr_item = (object)$item->delivery_receipt_details;
                foreach ($dr_item as $dr_item) {
                    $dr_item_desc = $dr_item->description;
                }


                $collect->put('type', 'Invoice');
                $collect->put('date', $item->created_at->toFormattedDateString());
                $collect->put('dateSales', $request->created_at->toFormattedDateString());
                $collect->put('sales_order_id', $request->id);
                $collect->put('dr_id', $item->id);
                $collect->put('name', $client->name);
                $collect->put('memo', $request->note);
                $collect->put('class', $client->class);
                $collect->put('desc', $dr_item_desc);
                $collect->put('qty', $item->total_qty);

                // $total_qty += $dr_qty;

                $collect->put('item', $item);
                array_push($data, $collect);
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

            $tbl = DeliveryReceipt::with(['sales_order', 'delivery_receipt_details'])
                ->join('delivery_receipt_item', 'delivery_receipts.id', 'delivery_receipt_item.delivery_receipt_id')
                ->where('item_id', $item)
                ->selectRaw('*,sum(delivery_receipt_item.qty)as total_qty ')
                ->groupBy('id')
                ->get();


            $tbl2 = SalesReturn::with("sales_return_details");
            $tbl3 = DB::table('stocks')->where("item_id", $item)->get();


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

            // return $tbl;
            // for DR & S.O
            foreach ($tbl as $item) {
                $total_delivered_qty = 0;

                $collect = collect();
                $client = (object)$item->sales_order->client;
                $request = (object)$item->sales_order;
                $dr_item = (object)$item->delivery_receipt_details;
                foreach ($dr_item as $dr_item) {
                    $dr_item_desc = $dr_item->description;
                }


                $collect->put('type', 'Invoice');
                $collect->put('date', $item->created_at->toFormattedDateString());
                $collect->put('dateSales', $request->created_at->toFormattedDateString());
                $collect->put('sales_order_id', $request->id);
                $collect->put('dr_id', $item->id);
                $collect->put('name', $client->name);
                $collect->put('memo', $request->note);
                $collect->put('class', $client->class);
                $collect->put('desc', $dr_item_desc);
                $collect->put('qty', $item->total_qty);

                // $total_qty += $dr_qty;

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

    public function componentSearch(Request $request)
    {
        $tbl1 = DB::table('warehouses')->where('name', 'like', '%' . $request->warehouse . '%')->get();
        $tbl2 = DB::table('categories')->where('name', 'like', '%' . $request->category . '%')->get();
        $tbl3 = DB::table('company_assets_types')->where('type_name', 'like', '%' . $request->type . '%')->get();
        $tbl4 = DB::table('company_assets')->where('name', 'like', '%' . $request->asset . '%')->get();
        $tbl5 = DB::table('mode_of_payments')->where('mode', 'like', '%' . $request->mode . '%')->get();
        $tbl6 = DB::table('terms')->where('term', 'like', '%' . $request->term . '%')->get();

        $data = collect();
        $data->put("warehouse", $tbl1);
        $data->put("category", $tbl2);
        $data->put("type", $tbl3);
        $data->put("asset", $tbl4);
        $data->put("mode", $tbl5);
        $data->put("term", $tbl6);

        return response()->json($data);
    }
}
