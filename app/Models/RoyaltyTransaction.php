<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoyaltyTransaction extends Model
{
    protected $fillable = ['author_id', 'order_item_id', 'amount'];
}
