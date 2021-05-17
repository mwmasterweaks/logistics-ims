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
        $clients = Client::on("mysqlis")->orderBy("name")->take(500)->get();
        return response()->json($clients);
    }

    public function show($id)
    {
        $client = Client::on("mysqlis")->find($id);

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
        $clients = Client::on("mysqlis")
            ->where('name', 'like', '%' . $request->client . '%')->get();
        return response()->json($clients);
    }

    public function countClient()
    {
        $clients = Client::all();
        return count($clients);
    }
}
