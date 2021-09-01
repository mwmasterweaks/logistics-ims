<?php

namespace App\Http\Controllers;

use App\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $temp = [];
        $purchase_orders = PurchaseOrder::with('user')
            ->with('supplier')
            ->with('item')
            ->orderBy('id', 'desc')
            ->get();

        foreach ($purchase_orders as $purchase_order) {
            $stocksQty = DB::table('stocks')
                ->where('purchase_order_id', $purchase_order->id)
                ->sum('qty_in');

            $orderedQty = DB::table('item_purchase_order')
                ->where('purchase_order_id', $purchase_order->id)
                ->sum('qty');

            $collection = collect($purchase_order);
            $collection->put('total_qty_ordered', $orderedQty);
            $collection->put('total_qty_received', $stocksQty);
            array_push($temp, $collection->all());
        }

        $purchase_orders = $temp;

        return response()->json($purchase_orders);
    }

    public function store(Request $request)
    {
        $purchase_order = PurchaseOrder::create([
            'user_id' => $request->id,
            'status' => 'draft'
        ]);

        return response()->json($purchase_order);
    }

    public function show($id)
    {
        $purchase_order = PurchaseOrder::with('user')
            ->with('supplier')
            ->with('item')
            ->where('id', $id)
            ->first();

        $temp = [];
        foreach ($purchase_order->item as $item) {
            $item = (object)$item;
            $collection = collect($item);

            $total_qty_received = DB::table('stocks')
                ->where('purchase_order_id', $id)
                ->where('item_id', $item->id)
                ->sum('qty_in');

            $type = DB::table('types')
                ->where('id', $item->type_id)->first();

            $collection->put('total_qty_received', $total_qty_received);
            $collection->put('type', $type);
            array_push($temp, $collection->all());
        }

        $collection = collect($purchase_order);
        $collection->put('item', $temp);
        $purchase_order = $collection->all();

        return response()->json($purchase_order);
    }

    public function update(Request $request, $id)
    {
        $request->supplier = (object)$request->supplier;
        $request->amount = (object)$request->amount;

        try {
            $supplier_id = $request->supplier->id;
        } catch (\Throwable $th) {
            $supplier_id = null;
        }

        $shipping_fee = $request->amount->shipping;
        $total = $request->amount->total;
        $tax = $request->amount->tax;

        DB::table('purchase_orders')
            ->where('id', '=', $id)
            ->update([
                'delivery_date' => $request->delivery_date,
                'shipping_fee' => $shipping_fee,
                'tax' => $tax,
                'total' => $total,
                'supplier_id' => $supplier_id,
                'updated_at' => \Carbon\Carbon::now()
            ]);

        DB::table('item_purchase_order')->where('purchase_order_id', $id)->delete();

        foreach ($request->orders as $order) {
            $order = (object)$order;
            $order->pivot = (object)$order->pivot;

            $collection = collect($order);

            try {
                $collection->put('qty', $order->pivot->qty);
            } catch (\Throwable $th) {
                $collection->put('qty', null);
            }

            try {
                $collection->put('unit', $order->pivot->unit);
            } catch (\Throwable $th) {
                $collection->put('unit', null);
            }


            try {
                $collection->put('price', $order->pivot->price);
            } catch (\Throwable $th) {
                $collection->put('price', null);
            }

            try {
                $collection->put('tax', $order->pivot->tax_rate);
            } catch (\Throwable $th) {
                $collection->put('tax', null);
            }

            $order = $collection->all();
            $order = (object)$order;
            $order->pivot = (object)$order->pivot;

            DB::table('item_purchase_order')->insert(
                [
                    'purchase_order_id' => $id,
                    'item_id' => $order->id,
                    'qty' => $order->qty,
                    'unit' => $order->unit,
                    'price' => $order->price,
                    'tax_rate' => $order->tax,
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now()
                ]
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param App\PurchaseOrder $purchase_order
     * @return \Illuminate\Http\Response
     */
    public function destroy($purchase_order)
    {
        try {
            PurchaseOrder::destroy($purchase_order);
            return $this->index();
        } catch (\Exception $e) {
            return response(['Problem deleting data', 500]);
        }
    }

    public function submitApproval($id)
    {
        DB::table('purchase_orders')
            ->where('id', $id)
            ->update([
                'status' => "approval",
                'updated_at' => \Carbon\Carbon::now()
            ]);
    }

    public function submitSupplier($id)
    {
        DB::table('purchase_orders')
            ->where('id', $id)
            ->update([
                'status' => "on order",
                'updated_at' => \Carbon\Carbon::now()
            ]);
    }

    public function approve($id)
    {
        DB::table('purchase_orders')
            ->where('id', $id)
            ->update([
                'status' => "approved",
                'updated_at' => \Carbon\Carbon::now()
            ]);
    }

    public function decline($id)
    {
        DB::table('purchase_orders')
            ->where('id', $id)
            ->update([
                'status' => "declined",
                'updated_at' => \Carbon\Carbon::now()
            ]);
    }
}
