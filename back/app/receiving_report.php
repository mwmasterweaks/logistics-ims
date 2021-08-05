<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class receiving_report extends Model
{
    protected $fillable = [
        'user_id', 'supplier_id', 'purchase_no', 'direct_no', 'invoice_num', 'freight', 'date_received'
    ];
}
