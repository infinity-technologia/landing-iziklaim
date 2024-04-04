<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogUsersUuid extends Model
{
    use HasFactory;
    protected $table = 'log_users_uuid';
    public $timestamps = false;
}
