<?php

namespace Ikechukwukalu\Tokenmiddleware\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class OldToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
    ];

    protected $hidden = [
        'token'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
