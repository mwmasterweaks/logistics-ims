<?php

namespace App\Http\Controllers;

use Auth;
use App\Item;
use App\User;
use App\Type;
use App\SalesOrder;
use App\DeliveryReceipt;
use App\Events\NotifyCreatedSalesOrder;
use App\Events\NotifyUpdatedSalesOrder;
use App\Events\NotifyUpdatedItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class SalesOrderController extends Controller
{
    public function index()
    {
        $temp_sales_orders = [];

        $sales_orders = SalesOrder::all();

        foreach ($sales_orders as $sales_order) {
            $total_delivered_qty = 0;
            $total_sales_ordered_qty = 0;
            $percent = 0;

            $delivery_receipts = DB::table('delivery_receipts')
                ->where('sales_order_id', $sales_order->id)->get();

            $total_sales_ordered_qty = DB::table('item_sales_order')
                ->where('sales_order_id', $sales_order->id)
                ->sum('qty');

            foreach ($delivery_receipts as $delivery_receipt) {
                if ($delivery_receipt->status != 'for delivery') {
                    $delivered_qty = DB::table('delivery_receipt_item')
                        ->where('delivery_receipt_id', $delivery_receipt->id)
                        ->sum('qty');

                    $total_delivered_qty += $delivered_qty;
                }
            }

            if ($total_delivered_qty > 0 && $total_sales_ordered_qty > 0) {
                $percent = round(($total_delivered_qty / $total_sales_ordered_qty) * 100);
            }

            $collection = collect($sales_order);
            $collection->put('percent', $percent);
            $collection->put('total_delivered_qty', $total_delivered_qty);
            $collection->put('total_sales_ordered_qty', $total_sales_ordered_qty);
            array_push($temp_sales_orders, $collection->all());
        }

        $sales_orders = $temp_sales_orders;
        return response()->json($sales_orders);
    }

    public function store(Request $request)
    {
        $sales_order = SalesOrder::create([
            'user_id' => $request->id
        ]);

        return response()->json($sales_order);
    }

    public function show($id)
    {
        $orders = [];

        $sales_order = SalesOrder::with(['client', 'user'])->find($id);

        $sales_order_details = DB::table('item_sales_order')
            ->where('sales_order_id', $id)->get();

        $delivery_receipts = DB::table('delivery_receipts')
            ->where('sales_order_id', $id)->get();

        if (!empty($sales_order_details)) {
            foreach ($sales_order_details as $order) {
                $total_delivered_qty = 0;
                $item = (object) app(ItemsController::class)->show($order->item_id);
                $item = (object) $item->original;

                if (!empty($delivery_receipts)) {
                    foreach ($delivery_receipts as $delivery_receipt) {
                        if ($delivery_receipt->status != 'for delivery') {
                            $total_delivered_qty += DB::table('delivery_receipt_item')
                                ->where('delivery_receipt_id', $delivery_receipt->id)
                                ->where('item_id', $order->item_id)
                                ->sum('qty');
                        }
                    }
                }

                $collection = collect($item);
                $collection->put('delivered_qty', $total_delivered_qty);
                $collection->put('price', $order->price);
                $collection->put('ordered_qty', $order->qty);
                array_push($orders, $collection->all());
            }
        }

        $collection = collect($sales_order);
        $collection->put('sales_order_details', $orders);
        $sales_order = $collection->all();

        return response()->json($sales_order);
    }

    public function showforReceipt($id)
    {
        $orders = [];

        $sales_order = SalesOrder::with(['client', 'user'])
            ->find($id);

        $sales_order_details = DB::table('item_sales_order')
            ->where('sales_order_id', $id)
            ->get();

        $delivery_receipts = DB::table('delivery_receipts')
            ->where('sales_order_id', $id)->get();

        if (!empty($sales_order_details)) {
            foreach ($sales_order_details as $order) {
                $total_delivered_qty = 0;
                $item = (object) app(ItemsController::class)->show($order->item_id);
                $item = (object) $item->original;

                if (!empty($delivery_receipts)) {
                    foreach ($delivery_receipts as $delivery_receipt) {
                        if ($delivery_receipt->status != 'for delivery') {
                            $total_delivered_qty += DB::table('delivery_receipt_item')
                                ->where('delivery_receipt_id', $delivery_receipt->id)
                                ->where('item_id', $order->item_id)
                                ->sum('qty');
                        }
                    }
                }

                $collection = collect($item);
                $collection->put('price', $order->price);
                $collection->put('ordered_qty', $order->qty);
                $collection->put('delivered_qty', $total_delivered_qty);
                $collection->put('delivering_qty', 0);
                $collection->put('ordered_serial', []);
                array_push($orders, $collection->all());
            }
        }

        $collection = collect($sales_order);
        $collection->put('sales_order_details', $orders);
        $sales_order = $collection->all();

        return  response()->json($sales_order);
    }

    public function update(Request $request, $id)
    {
        $request = (object) $request;
        $request->client = (object) $request->client;
        $request->user = (object) $request->user;
        $request->amount = (object) $request->amount;

        try {
            $client_id = $request->client->id;
        } catch (\Throwable $th) {
            $client_id = null;
        }

        DB::table('sales_orders')
            ->where('id', $id)
            ->update([
                'client_id' => $client_id,
                'user_id' => $request->user->id,
                'note' => $request->note,
                'updated_at' => Carbon::now()
            ]);

        DB::table('item_sales_order')->where('sales_order_id', $id)->delete();

        foreach ($request->orders as $order) {
            $order = (object) $order;
            //$order->type = (object)$order->type;

            DB::table('item_sales_order')->insert(
                [
                    'sales_order_id' => $id,
                    'item_id' => $order->id,
                    'qty' => $order->ordered_qty,
                    'price' => $order->price,
                ]
            );
        }

        //event(new NotifyUpdatedSalesOrder($sales_order));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param App\SalesOrder $sales_order
     * @return \Illuminate\Http\Response
     */
    public function destroy($sales_order)
    {
        try {
            SalesOrder::destroy($sales_order);
            return "ok";
        } catch (\Exception $e) {
            return response(['Problem deleting data', 500]);
        }
    }

    public function showSearch(Request $request)
    {
        $temp_sales_orders = [];

        if ($request->status == "All") {
            $request->status = "";
        }

        if ($request->sort == "Latest") {
            $sales_orders = SalesOrder::with('sales_order_details')
                ->with('client')->with('user')
                ->where('id', 'like', '%' . $request->sales_order . '%')
                ->where('status', 'like', '%' . $request->status . '%')
                ->orderBy('id', 'desc')->get();
        } else if ($request->sort == "Oldest") {
            $sales_orders = SalesOrder::with('sales_order_details')
                ->with('client')->with('user')
                ->where('id', 'like', '%' . $request->sales_order . '%')
                ->where('status', 'like', '%' . $request->status . '%')
                ->orderBy('id', 'asc')->get();
        }

        foreach ($sales_orders as $sales_order) {
            $total_delivered_qty = 0;
            $total_sales_ordered_qty = 0;
            $percent = 0;

            $delivery_receipts = DB::table('delivery_receipts')
                ->where('sales_order_id', $sales_order->id)->get();

            $total_sales_ordered_qty = DB::table('item_sales_order')
                ->where('sales_order_id', $sales_order->id)
                ->sum('qty');

            foreach ($delivery_receipts as $delivery_receipt) {
                if ($delivery_receipt->status != 'for delivery') {
                    $delivered_qty = DB::table('delivery_receipt_item')
                        ->where('delivery_receipt_id', $delivery_receipt->id)
                        ->sum('qty');

                    $total_delivered_qty += $delivered_qty;
                }
            }

            if ($total_delivered_qty > 0 && $total_sales_ordered_qty > 0) {
                $percent = round(($total_delivered_qty / $total_sales_ordered_qty) * 100);
            }

            $collection = collect($sales_order);
            $collection->put('percent', $percent);
            $collection->put('total_delivered_qty', $total_delivered_qty);
            $collection->put('total_sales_ordered_qty', $total_sales_ordered_qty);
            array_push($temp_sales_orders, $collection->all());
        }

        $sales_orders = $temp_sales_orders;
        return response()->json($sales_orders);
    }

    public function showClientOrdered($id)
    {
        $sales_order = SalesOrder::with('sales_order_details')
            ->where('client_id', $id)->get();

        $items = [];

        foreach ($sales_order as $order) {
            $delivery_receipt = DeliveryReceipt::with('sales_order')
                ->where('sales_order_id', $order->id)->first();

            if (!empty($delivery_receipt)) {
                foreach ($order->sales_order_details as $item) {
                    $delivery_receipt = (object) $delivery_receipt;

                    $collection = collect($item);
                    $collection->put('delivery_receipt', $delivery_receipt);

                    array_push($items, $collection->all());
                }
            }
        }

        return response()->json($items);
    }

    public function countPending()
    {
        $pending = SalesOrder::where('status', '=', 'approval')->get();
        return count($pending);
    }

    public function countVerified()
    {
        $verified = SalesOrder::where('status', 'verified')->orWhere('status', 'approved')->get();
        return count($verified);
    }

    public function countDeclined()
    {
        $canceled = SalesOrder::where('status', 'declined')->get();
        return count($canceled);
    }

    public function submitApproval($id)
    {
        DB::table('sales_orders')
            ->where('id', $id)
            ->update([
                'status' => "approval",
                'updated_at' => \Carbon\Carbon::now()
            ]);

        //$sales_order = SalesOrder::with(['client', 'user'])->find($id);
        //broadcast(new NotifyCreatedSalesOrder($sales_order));

        // event(new NotifyCreatedSalesOrder($sales_order));
    }

    public function approve($id)
    {
        DB::table('sales_orders')
            ->where('id', $id)
            ->update([
                'status' => "approved",
                'updated_at' => \Carbon\Carbon::now()
            ]);

        $sales_order = SalesOrder::with(['client', 'user'])->find($id);
        //broadcast(new NotifyUpdatedSalesOrder($sales_order));
    }

    public function decline($id)
    {
        DB::table('sales_orders')
            ->where('id', $id)
            ->update([
                'status' => "declined",
                'updated_at' => \Carbon\Carbon::now()
            ]);

        $sales_order = SalesOrder::with(['client', 'user'])->find($id);
        //broadcast(new NotifyUpdatedSalesOrder($sales_order));
    }

    public function item_exist($item_id, $orders, $serial)
    {
        $bool = false;
        $index = 0;

        foreach ($orders as $item) {
            $item = (object) $item;

            if ($item->item_id == $item_id) {
                $bool = true;

                break;
            }

            $index++;
        }

        return array($bool, $index);
    }

    public function searchItem(Request $request)
    {
        $temp = Item::with(['type', 'category', 'stocks'])
            ->where('description', 'like', $request->item . '%')
            ->get();
        return response()->json($temp);
    }
}
