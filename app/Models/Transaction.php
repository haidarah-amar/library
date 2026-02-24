<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'bill_id',
        'book_id',
        'price',
        'mortgage',
        'extra_price',
        'status',
        'delivered_at',
        'due_date',
        'returned_at',
        'customer_return_amount'
    ];
   public function bill()
{
    return $this->belongsTo(Bill::class);
}

public function book()
{
    return $this->belongsTo(Book::class);
}
}
