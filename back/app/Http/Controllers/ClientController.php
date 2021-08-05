<?php

namespace App\Http\Controllers;

use App\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ClientController extends Controller
{
    public function index()
    {
        // $clients = Client::on("mysqlis")->orderBy("name")->take(100)->get();
        // return response()->json($clients);
        $clients = DB::table('clients')->orderBy("name")->get();

        return response()->json($clients);
    }

    public function show($id)
    {
        $client =
            DB::table('clients')->where('id', '=', $id)->first();

        if (!empty($client))
            return response()->json($client);

        return response()->json(['error' => 'Resource not found!'], 404);
    }

    public function store(Request $request)
    {
        $client = Client::where('name', '=', ucwords($request->name))->first();

        //return $request;
        if (empty($client)) {
            try {
                //Client::create($request->all());

                Client::create([
                    'name' => $request->name,
                    'contact' => $request->contact,
                    'address' => $request->address,
                    'client_type' => $request->client_type
                ]);
            } catch (\Exception $ex) {
                return response()->json(['error' =>  $ex->getMessage()], 500);
            }
        } else {
            return response()->json(['error' => 'Client already exists!'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        DB::table('clients')
            ->where('id', $id)
            ->update([
                'name' => $request->name,
                'address' => $request->address,
                'client_type' => $request->client_type,
                'contact' => $request->contact
            ]);

        return response()->json($this->index()->original);
    }

    public function showSearch(Request $request)
    {
        // $clients = DB::table('clients')
        //     ->where('name', 'like', '%' . $request->client . '%')->get();
        $clients = DB::table('clients')
            // ->select('name')
            ->where('name', 'like', $request->client . '%')
            ->get();
        return response()->json($clients);
    }

    public function countClient()
    {
        $clients = Client::all();
        return count($clients);
    }

    public function updateClients()
    {
        // return "connected";
        // $clients = Client::on("mysqlis")->orderBy("name")->take(500)->get();
        try {
            $current = Client::max('id');
            $dataSet = Client::on("mysqlis")->where("id", ">", $current)->take(100)->get();
            // return $dataSet;
            $temp = [];
            foreach ($dataSet as $i => $d) {
                $temp[$i] = [
                    'id' => $d->id,
                    'account_no' => $d->account_no,
                    'branch_id' => $d->branch_id,
                    'name' => $d->name,
                    'owner_name' => $d->owner_name,
                    'location' => $d->location,
                    'contact_person' => $d->contact_person,
                    'business_type' => $d->business_type,
                    'contact' => $d->contact,
                    'email_add' => $d->email_add


                ];
            }
            DB::table('clients')->insert($temp);

            return "ok";
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }
}
