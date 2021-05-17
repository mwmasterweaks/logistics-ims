<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name', 'contact', 'address', 'client_type'
    ];

    protected $table = 'clients';
    protected $connection = 'mysqlis';

    public function __construct(array $attributes = [])
    {
        $this->table = env('DB_DATABASE_IS') . '.' . $this->table;
        parent::__construct();
    }
}
