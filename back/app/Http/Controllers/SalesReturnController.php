<?php

namespace App\Http\Controllers;

use App\SalesReturn;
use App\Item;
use App\Client;
use App\DeliveryReceipt;
use App\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use PhpParser\Node\Stmt\Foreach_;

class SalesReturnController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $SalesReturn = SalesReturn::all();
        $container = array();
        foreach ($SalesReturn as $sret) {
            $clientFrom =  DB::table('clients')
                ->where('id', $sret->from_client_id)->first();

            $temp = (object)[
                'id' => $sret->id,
                'from_name' => $clientFrom->name,
                'from_contact' => $clientFrom->contact,
                'from_address' => $clientFrom->location,
                'returnee' => $sret->returnee,
                'status' => $sret->status,
                'remarks' => $sret->remarks,
                'date_return' => $sret->date_return,
            ];
            array_push($container, $temp);
        }
        return response()->json($container);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $items = (object)$request->items;
            $request->clientFrom = (object)$request->clientFrom;
            $request->clientTo = (object)$request->clientTo;

            $receive_date = date_create($request->date_recieve);
            $receive_date = date_format($receive_date, "Y-m-d H:i:s");



            $id = DB::table('sales_returns')->insertGetId(
                [
                    'from_client_id' => $request->clientFrom->id,
                    'to_client_id' => null,
                    'returnee' => $request->returnee,
                    'status' =>
                    $request->status,
                    'remarks' => $request->remarks,
                    'date_return' => $receive_date,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
            foreach ($items as $item) {

                $item = (object)$item;
                DB::table('sales_return_item')->insert(
                    [
                        'sales_return_id' => $id,
                        'item_id' => $item->id,
                        'serial' => $item->serial,
                        'date_return' => $receive_date,
                        'qty' => $item->qty,
                        'received_to' => $item->received_to,
                        'status' => $item->status,
                    ]
                );
            }

            DB::commit();
            return response()->json($items);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response([$ex, 500]);
        }
    }

    public function approve($id)
    {
        try {
            DB::beginTransaction();


            $return1 = DB::table('sales_return_item')
                ->where('sales_return_id', $id)
                ->first();

            $return2 = DB::table('sales_returns')
                ->where('id', $id)
                ->first();


            $retSales = DB::table('sales_orders')
                ->where("client_id", $return2->from_client_id)
                ->first();

            $retDelivery = DB::table('delivery_receipts')
                ->where("sales_order_id", $retSales->id)
                ->first();

            $retItem =
                DB::table('delivery_receipt_item')
                ->where("delivery_receipt_id", $retDelivery->id)
                ->where("item_id", $return1->item_id)
                ->where("barcode", $return1->serial);

            $retItemCount = $retItem->count();

            if ($retItemCount > 0) {

                DB::table('sales_returns')
                    ->where('id', $id)
                    ->update([
                        'status' => "Approved",
                        'updated_at' => Carbon::now()
                    ]);

                // $addQty = $retItem->increment("qty_return", 1);

                $itemTemp = Item::with(['type', 'category', 'stocks'])
                    ->find($return1->item_id);



                if ($return1->serial == null) {
                    if ($return1->status != 'Defective') {
                        DB::table('stocks')->insert(
                            [
                                'item_id' => $itemTemp->id,
                                'warehouse_id' => $return1->received_to,
                                'received_at' => $return2->date_return,
                                'qty_in' => $return1->qty,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ]
                        );
                    }
                } else {

                    $serial = DB::table('stock_serial')->where('serial', $return1->serial)->first();


                    if ($return1->status == 'Defective') {

                        DB::table('stock_serial')
                            ->where('serial', $return1->serial)
                            ->update([
                                'status' => 'Defective'
                            ]);
                    } else {
                        DB::table('stocks')
                            ->where('id', $serial->stock_id)
                            ->increment(
                                'qty_in',
                                $return1->qty
                            );

                        DB::table('stock_serial')
                            ->where('serial', $return1->serial)
                            ->update([
                                'status' => 'stocked in'
                            ]);
                    }
                }

                DB::table('logs')->insert(
                    [
                        'item_id' => $return1->item_id,
                        'serial' => $return1->serial,
                        'status' => $return1->status,
                        'remarks' => ' Return Quantity of ' . $return1->qty,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]
                );
                DB::commit();
                return response()->json("Data inserted!");
            } else {
                return response()->json("Data doesn't exist!!");
            }



            DB::commit();
            return $this->index();
        } catch (\Exception $ex) {
            //throw $th;
            DB::rollBack();
            return response([$ex, 500]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SalesReturn  $salesReturn
     * @return \Illuminate\Http\Response
     */
    public function show($salesReturn_id)
    {
        $SalesReturn = SalesReturn::find($salesReturn_id);
        $clientFrom =  DB::table('clients')
            ->where('id', $SalesReturn->from_client_id)->first();
        $clientTo =  DB::table('clients')
            ->where('id', $SalesReturn->to_client_id)->first();
        $items = DB::table('items')
            ->join('sales_return_item', 'sales_return_item.item_id', 'items.id')
            ->where('sales_return_item.sales_return_id', $SalesReturn->id)
            ->get();
        $container = (object)[
            'id' => $SalesReturn->id,
            'from_name' => $clientFrom->name,
            'from_contact' => $clientFrom->contact,
            'from_address' => $clientFrom->location,
            // 'to_name' => $clientTo->name,
            // 'to_contact' => $clientTo->contact,
            // 'to_address' => $clientTo->location,
            'returnee' => $SalesReturn->returnee,
            'status' => $SalesReturn->status,
            'remarks' => $SalesReturn->remarks,
            'date_return' => $SalesReturn->date_return,
            'items' => $items
        ];
        return response()->json($container);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SalesReturn  $salesReturn
     * @return \Illuminate\Http\Response
     */
    public function edit(SalesReturn $salesReturn)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SalesReturn  $salesReturn
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SalesReturn  $salesReturn
     * @return \Illuminate\Http\Response
     */
    public function destroy(SalesReturn $salesReturn)
    {
        //
    }

    public function searchSalesReturn(Request $request)
    {
    }

    public function updateStatus(Request $request)
    {
        try {
            $status = $request->itemStatus;
            $items = $request->item;

            foreach ($items as $item) {

                $item = (object)$item;
                DB::table('sales_return_item')
                    ->where('item_id', '=', $item->id)
                    ->where('sales_return_id', '=', $request->id)
                    ->update(['status' => $request->itemStatus]);
            }

            return "ok";
        } catch (\Exception $ex) {
            DB::rollBack();
            return response([$ex, 500]);
        }
    }
}
