<?php

namespace App\Http\Controllers;

use App\direct_receive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class DirectReceiveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $container = [];
        $directReceive = direct_receive::with(['user', 'supplier', 'item', 'warehouse'])->orderBy('id', 'asc')->get();


        foreach ($directReceive as $sret) {

            $collection = collect($sret);
            array_push($container, $collection->all());
        }


        $directReceive = $container;
        return response()->json($directReceive);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\direct_receive  $direct_receive
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\direct_receive  $direct_receive
     * @return \Illuminate\Http\Response
     */
    public function edit(direct_receive $direct_receive)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\direct_receive  $direct_receive
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, direct_receive $direct_receive)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\direct_receive  $direct_receive
     * @return \Illuminate\Http\Response
     */
    public function destroy(direct_receive $direct_receive)
    {
        //
    }
}
