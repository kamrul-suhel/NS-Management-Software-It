<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use SoftDeletes;
    //
    protected $table='banks';

    protected $fillable = [
        'phone',
        'address',
        'name'
    ];


}
