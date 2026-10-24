<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteVisit extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = ['started_at' => 'immutable_datetime', 'last_seen_at' => 'immutable_datetime'];
}
