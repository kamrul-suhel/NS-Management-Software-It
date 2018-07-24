<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanyTransaction extends Model
{
    //

    protected $fillable = [
      'company_id',
      'payment_type',
      'reference',
        'remarks',
        'debit',
        'credit',
        'balance',
        'manuel_date'
    ];

    public function company(){
        return $this->belongsTo(Company::class);
    }


}
