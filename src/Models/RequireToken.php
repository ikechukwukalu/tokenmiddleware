<?php

namespace Ikechukwukalu\Tokenmiddleware\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class RequireToken extends Model
{
    use HasFactory;

    protected $table = 'require_tokens';

    protected $fillable = [
        'user_id',
        'uuid',
        'ip',
        'device',
        'payload',
        'method',
        'route_arrested',
        'redirect_to',
        'token_validation_url',
        'approved_at',
        'cancelled_at',
        'expires_at',
    ];

    protected $hidden = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
