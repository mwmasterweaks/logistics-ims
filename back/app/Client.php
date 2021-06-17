<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'account_no', 'branch_id', 'name', 'owner_name', 'location', 'contact_person', 'business_type', 'contact', 'email_add'
    ];
}
