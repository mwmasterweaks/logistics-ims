<?php

namespace App;
use App\SalesOrder;
use App\Item;

use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    protected $fillable = [
        'from_client_id', 'to_client_id', 'returnee', 'status', 'remarks', 'date_return',
    ];


}
