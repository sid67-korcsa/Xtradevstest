<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{

    /**
     * Default table name
     *
     * @var string
     */
    protected $table = "events";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'add_date',
        'desc',
        'limit',
        'image'
    ];

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_event', 'user_id', 'event_id');
    }

}
