<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BroadcastsTableUpdates;

/**
 * @property int $id
 * @property int $user_id
 * @property string $event_type
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $browser
 * @property string|null $device
 * @property string|null $platform
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class UserLog extends Model
{
    use HasFactory, BroadcastsTableUpdates;

    protected $table = 'user_logs';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'event_type',
        'ip_address',
        'user_agent',
        'browser',
        'device',     
        'platform',   
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $model->broadcastCreated('user_logs', $model);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
