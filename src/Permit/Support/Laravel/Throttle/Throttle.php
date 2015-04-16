<?php namespace Permit\Support\Laravel\Throttle;

use Illuminate\Database\Eloquent\Model;

class Throttle extends Model{

    protected $dates = [
        'last_attempt_at',
        'suspended_at',
        'banned_at'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'throttle';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

}