<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Test extends Model
{

    protected $table = "tests";

    protected $fillable = [
      "data",
      "comment",
      "modify"
    ];

    protected $guarded = [];


}
