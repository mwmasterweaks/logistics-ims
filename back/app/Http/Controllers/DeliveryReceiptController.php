<?php

namespace App\Http\Controllers;

use App\Item;
use App\SalesOrder;
use App\DeliveryReceipt;
use App\Client;
use App\Events\NotifyUpdatedSalesOrder;
use App\Events\NotifyUpdatedItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Database\QueryException;
use Carbon\Carbon;
use App\Log;

class DeliveryReceiptController extends Controller
{
    public function index()
    {
        $delivery_receipts = DeliveryReceipt::with(['sales_order', 'user'])
            ->orderBy('id', 'desc')->get();

        $temp_delivery_receipts = [];

        foreach ($delivery_receipts as $delivery_receipt) {

            $client = Client::find($delivery_receipt->sales_order->client_id);

            if (empty($client)) {
                $client = [];
            }

            $collection = collect($delivery_receipt);
            $collection->put('client', $client);
            array_push($temp_delivery_receipts, $collection->all());
        }

        $delivery_receipts = $temp_delivery_receipts;

        return response()->json($delivery_receipts);
    }

    public function store(Request $request, $id)
    {
        $temp = DB::table('sales_orders')->where('id', $id)->first();

        $delivery_receipt_id = DB::table('delivery_receipts')->insertGetId(
            [
                "sales_order_id" => $id,
                "memo" => $temp->note,
                "user_id" => $request->id,
                "status" => "for delivery",
                "created_at" =>  Carbon::now(),
                "updated_at" => Carbon::now()
            ]
        );

        //$sales_order = DB::table('sales_orders')
        //	->where('id', $id)->first();
        //broadcast(new NotifyUpdatedSalesOrder($sales_order));

        return response()->json($delivery_receipt_id);
    }

    public function show($id)
    {
        $delivery_receipt =
            DeliveryReceipt::with(['sales_order', 'user'])
            ->find($id);

        $temp_orders =
            DB::table('delivery_receipt_item')
            ->where('delivery_receipt_id', $id)
            ->get();

        $orders = [];


        if (!empty($temp_orders)) {

            // return $temp_orders;
            foreach ($temp_orders as $value) {
                $delivered_qty = 0;
                $item = (object) app(ItemsController::class)->show($value->item_id);
                $item = (object) $item->original;
                $item->type = (object) $item->type;


                if ($item->type->name == "Serialize") {

                    if ($this->item_exist($item->id, $orders, $value->barcode)[0]) {

                        $index = $this->item_exist($item->id, $orders, $value->barcode)[1];
                        $orders[$index] = (object) $orders[$index];
                        array_push($orders[$index]->ordered_serial, $value->barcode);
                    } else {

                        $temp_ordered_serial = [];
                        array_push($temp_ordered_serial, $value->barcode);


                        $sales_order_details =
                            DB::table('item_sales_order')
                            ->where('sales_order_id', $delivery_receipt->sales_order_id)
                            ->where('item_id', $item->id)
                            ->first();

                        if ($delivery_receipt->status != 'for delivery') {
                            $delivered_qty = DB::table('delivery_receipt_item')
                                ->where('delivery_receipt_id', $delivery_receipt->id)
                                ->where('item_id', $value->item_id)
                                ->sum('qty');
                        }

                        $collection = collect($item);
                        $collection->put('price', $value->price);
                        $collection->put('ordered_qty', $sales_order_details->qty);
                        $collection->put('delivering_qty', $value->qty);
                        // $collection->put('return_qty', $value->qty_return);
                        $collection->put('ordered_serial', $temp_ordered_serial);
                        $collection->put('delivered_qty', $delivered_qty);
                        array_push($orders, $collection->all());
                    }
                } else if ($item->type->name == "Consumable") {
                    $sales_order_details =
                        DB::table('item_sales_order')
                        ->where('sales_order_id', $delivery_receipt->sales_order_id)
                        ->where('item_id', $item->id)
                        ->first();

                    if ($delivery_receipt->status != 'for delivery') {
                        $delivered_qty = DB::table('delivery_receipt_item')
                            ->where('delivery_receipt_id', $delivery_receipt->id)
                            ->where('item_id', $value->item_id)
                            ->sum('qty');
                    }

                    $collection = collect($item);
                    $collection->put('price', $value->price);
                    $collection->put('delivering_qty', $value->qty);
                    // $collection->put('return_qty', $value->qty_return);
                    $collection->put('ordered_qty', $sales_order_details->qty);
                    $collection->put('delivered_qty', $delivered_qty);
                    array_push($orders, $collection->all());
                }
            }
        }


        $collection = collect($delivery_receipt);
        $collection->put('orders', $orders);
        $delivery_receipt = $collection->all();

        return response()->json($delivery_receipt);
    }

    public function update(Request $request, $id)
    {
        $request = (object) $request;

        DB::table('delivery_receipts')
            ->where('id', '=', $request->id)
            ->update(['updated_at' => Carbon::now()]);

        DB::table('delivery_receipt_item')->where('delivery_receipt_id', $request->id)->delete();

        foreach ($request->orders as $order) {
            $order = (object) $order;
            $order->type = (object) $order->type;

            if ($order->type->name == "Serialize") {
                $order->ordered_serial = (object) $order->ordered_serial;

                foreach ($order->ordered_serial as $serial) {

                    DB::table('delivery_receipt_item')->insert(
                        [
                            'delivery_receipt_id' => $id,
                            'item_id' => $order->id,
                            'barcode' => $serial,
                            'qty' => 1,
                            'price' => $order->price
                        ]
                    );
                }
            } else {
                DB::table('delivery_receipt_item')->insert(
                    [
                        'delivery_receipt_id' => $id,
                        'item_id' => $order->id,
                        'qty' => $order->delivering_qty,
                        'price' => $order->price
                    ]
                );
            }
        }
    }

    public function confirm($id)
    {
        DB::beginTransaction();
        try {
            DB::table('delivery_receipts')
                ->where('id', $id)
                ->update([
                    'status' => 'delivering',
                    'updated_at' => Carbon::now()
                ]);
            $orders = DB::table('delivery_receipt_item')
                ->where('delivery_receipt_id', '=', $id)->get();

            $client = DB::table('clients')
                ->join('sales_orders', 'clients.id', 'sales_orders.client_id')
                ->join('delivery_receipts', 'delivery_receipts.sales_order_id', 'sales_orders.id')
                ->where('delivery_receipts.id', $id)
                ->get();
            foreach ($orders as $order) {
                if (!empty($order->barcode)) {
                    DB::table('stock_serial')
                        ->where('serial', '=', $order->barcode)
                        ->update([
                            'status' => 'deployed',
                            'updated_at' => Carbon::now()
                        ]);
                    DB::table('stocks')
                        ->where('item_id', '=', $order->item_id)
                        ->increment('qty_out', 1);
                    $log = new Log;
                    $log->item_id = $order->item_id;
                    $log->serial = $order->barcode;
                    $log->status = 'deployed';
                    $log->remarks = 'Sold to ' . $client[0]->name;
                    $log->save();
                } else {
                    $order_remain = $order->qty;
                    $item_stocks = DB::table('stocks')
                        ->where('item_id', '=', $order->item_id)
                        ->orderBy('id', 'asc')->get();
                    foreach ($item_stocks as $stock) {
                        if (
                            $stock->qty_in > $stock->qty_out
                            && $order_remain > 0
                        ) {
                            $stock_available = $stock->qty_in - $stock->qty_out;
                            if ($stock_available >= $order_remain) {
                                DB::table('stocks')
                                    ->where('id', '=', $stock->id)
                                    ->increment('qty_out', $order_remain);
                                $order_remain = 0;
                            } else {
                                DB::table('stocks')
                                    ->where('id', '=', $stock->id)
                                    ->increment('qty_out', $stock_available);
                                $order_remain = $order_remain - $stock_available;
                            }
                        }
                        if ($order_remain < 1) {
                            break;
                        }
                    }
                    $log = new Log;
                    $log->item_id = $order->item_id;
                    $log->serial = $order->barcode;
                    $log->status = 'deployed';
                    $log->remarks = 'Sold to ' . $client[0]->name . ' Quantity ' . $order->qty;
                    $log->save();
                }
                //update sales order if all delivered items are completed
                $this->checkSalesOrderComplete($id);
                $delivery_receipt = DB::table('delivery_receipts')
                    ->where('id', $id)->first();
                $sales_order = DB::table('sales_orders')
                    ->where('id', $delivery_receipt->sales_order_id)->first();
                //broadcast(new NotifyUpdatedSalesOrder($sales_order));
            }
            DB::commit();
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex], 500);
        }
    }

    public function item_exist($item_id, $orders, $serial)
    {
        $bool = false;
        $index = 0;

        foreach ($orders as $item) {
            $item = (object) $item;

            if ($item->id == $item_id) {
                $bool = true;

                break;
            }

            $index++;
        }

        return array($bool, $index);
    }

    public function checkSalesOrderComplete($id)
    {
        $delivery_receipt = DB::table('delivery_receipts')
            ->where('id', $id)->first();

        $sales_order_details = DB::table('item_sales_order')
            ->where('sales_order_id', $delivery_receipt->sales_order_id)->get();

        $delivery_receipts = DB::table('delivery_receipts')
            ->where('sales_order_id', $delivery_receipt->sales_order_id)->get();

        $total_delivered_qty = 0;

        if (!empty($sales_order_details)) {
            foreach ($sales_order_details as $ordered) {
                foreach ($delivery_receipts as $receipt) {
                    if ($receipt->status != 'for delivery') {
                        $total_delivered_qty += DB::table('delivery_receipt_item')
                            ->where('delivery_receipt_id', $receipt->id)
                            ->where('item_id', $ordered->item_id)
                            ->sum('qty');
                    }
                }
            }
        }

        $total_sales_order_qty = DB::table('item_sales_order')
            ->where('sales_order_id', $delivery_receipt->sales_order_id)
            ->sum('qty');

        if ($total_delivered_qty == $total_sales_order_qty) {
            DB::table('sales_orders')
                ->where('id', $delivery_receipt->sales_order_id)
                ->update([
                    'status' => 'order complete',
                    'updated_at' => Carbon::now()
                ]);
        }
    }

    public function changeStatus($id)
    {
        try {
            DB::table('delivery_receipts')
                ->where('id', '=', $id)
                ->update(['status' => 'delivered', 'updated_at' => Carbon::now()]);
            return 'ok';
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json(["error" => "Update status failed please try again"], 500);
        }
    }

    // summary report
}
