<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogResetPassword extends Model
{
    use HasFactory;
    protected $table = 'log_reset_password';
    public $timestamps = false;
}
