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
      private $cname = "SalesReturnController";
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $SalesReturn = SalesReturn::with(['sales_return_details', 'client'])->orderBy('id', 'desc')->get();
        $container = [];
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
        // return $SalesReturn;
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
            $request->user = (object) $request->user;

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
                DB::table('item_sales_return')->insert(
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

              \Logger::instance()->log(
                Carbon::now(),
                $request->user->id,
                $request->user->name,
                $this->cname,
                "store",
                "message",
                "Create new Sales Return: #" . $id . ", For Client: " .  $request->clientFrom->id
            );

            DB::commit();
            return response()->json($items);
        } catch (\Exception $ex) {
            DB::rollBack();
             \Logger::instance()->log(
                Carbon::now(),
                $request->user_id,
                $request->user_name,
                $this->cname,
                "store",
                "Error",
                $ex->getMessage()
            );
            return response([$ex, 500]);
        }
    }

    public function approve(Request $request)
    {
        try {
            DB::beginTransaction();
            // return $request;

            $return_details = (object)$request->return_details;
            $items = (object)$request->return_items;
            $request->user = (object) $request->user;
            $client = $return_details->from_id;


            $return1 = DB::table('item_sales_return')
                ->where('sales_return_id', $return_details->id)
                ->first();

            $return2 = DB::table('sales_returns')
                ->where('id', $return_details->id)
                ->first();


            $retSales = DB::table('sales_orders')
                ->where("client_id", $client)
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

            // return $retItemCount;

            if ($retItemCount > 0) {

                DB::table('delivery_receipt_item')
                    ->where("delivery_receipt_id", $retDelivery->id)
                    ->where("item_id", $return1->item_id)
                    ->where("barcode", $return1->serial)
                    ->increment("qty_return", 1);

                DB::table('sales_returns')
                    ->where('id', $return_details->id)
                    ->update([
                        'status' => "Approved",
                        'updated_at' => Carbon::now()
                    ]);


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
                                'status' => 'Defective',
                                'updated_at' => Carbon::now()
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
                                'status' => 'stocked in',
                                'updated_at' => Carbon::now()
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


            //     \Logger::instance()->log(
            //     Carbon::now(),
            //     $request->user->id,
            //     $request->user->name,
            //     $this->cname,
            //     "approve",
            //     "message",
            //     "Approve Sales Return: #" . $return_details->id 
            // );
                return "Data inserted!";
            } else {
                return "Data doesn't exist!!";
            }


            // DB::commit();
            return $this->index();
        } catch (\Exception $ex) {
            //throw $th;
            DB::rollBack();
             \Logger::instance()->log(
                Carbon::now(),
                $request->user_id,
                $request->user_name,
                $this->cname,
                "store",
                "Error",
                $ex->getMessage()
            );
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
            ->join('item_sales_return', 'item_sales_return.item_id', 'items.id')
            ->where('item_sales_return.sales_return_id', $SalesReturn->id)
            ->get();
        $container = (object)[
            'id' => $SalesReturn->id,
            'from_id' => $SalesReturn->from_client_id,
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

     public function countPending()
    {
        $pending = SalesReturn::where('status', '=', 'For Approval')->get();
        return count($pending);
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
    public function destroy($salesReturn)
    {
        try {
            SalesReturn::destroy($salesReturn);
            return "ok";
        } catch (\Exception $e) {
            return response(['Problem deleting data', 500]);
        }
    }

    public function searchSalesReturn(Request $request)
    {
        $temp_sales_returns = [];
        $filter = $request->filter;

        if ($request->date_to == null)
            $request->date_to = $request->date_from;


        $from = new Carbon($request->date_from);
        $to = new Carbon($request->date_to);
        $to = $to->addDay();

        $sales_returns = SalesReturn::with(['sales_return_details', 'client']);

        if ($request->sort == "1") {
            $sales_returns = SalesReturn::with(['sales_return_details', 'client'])
                ->orderBy('id', 'desc');
        } else if ($request->sort == "2") {
            $sales_returns = SalesReturn::with(['sales_return_details', 'client'])
                ->orderBy('id', 'asc');
        }

        if ($filter == "number") {
            if ($request->number != null) {
                $sales_returns = $sales_returns->where('id', $request->number)->get();
            } else $sales_returns = $sales_returns->get();
        } else if ($filter == "returnee") {
            if ($request->returnee != null) {
                $sales_returns = $sales_returns->where('returnee', 'like', '%' . $request->returnee . '%')->get();
            } else $sales_returns = $sales_returns->get();
        } else if ($filter == "date") {
            if ($from != null && $to != null) {
                $sales_returns = $sales_returns->whereBetween("created_at", [$from->toDateString(), $to->toDateString()])->get();
            } else $sales_returns = $sales_returns->get();
        } else if ($filter == "status") {
            if ($request->statusSelected != null) {
                $sales_returns = $sales_returns->where("status", $request->statusSelected)->get();
            } else $sales_returns = $sales_returns->get();
        }

        foreach ($sales_returns as $sales_return) {

            $client = Client::find($sales_return->from_client_id);

            if (empty($client)) {
                $client = [];
            }
            $collection = collect($sales_return);
            $collection->put('from_name', $client->name);
            array_push($temp_sales_returns, $collection->all());
        }

        $sales_returns = $temp_sales_returns;
        return response()->json($sales_returns);
    }

    public function updateStatus(Request $request)
    {
        try {

            $request->user = (object) $request->user;
            $return = $request->id;
            foreach ($request->item as $item) {

                $item = (object)$item;
                DB::table('item_sales_return')
                    ->where('item_id', '=', $item->id)
                    ->where('sales_return_id', '=', $return)
                    ->update(['status' => $item->status]);

                    // logger
                     \Logger::instance()->log(
                Carbon::now(),
                $request->user->id,
                $request->user->name,
                $this->cname,
                "update",
                "message",
                "Update Item Status:" . $item->id . ", For Sales Return #: " . $return
            );
            }

             

            return "ok";
        } catch (\Exception $ex) {
            DB::rollBack();
             \Logger::instance()->log(
                Carbon::now(),
                $request->user_id,
                $request->user_name,
                $this->cname,
                "store",
                "Error",
                $ex->getMessage()
            );
            return response([$ex, 500]);
        }
    }
}
