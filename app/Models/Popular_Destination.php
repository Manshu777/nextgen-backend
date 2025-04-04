<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Popular_Destination extends Model
{
    use HasFactory;
 protected $table="popular__destinations_flight";

 protected $fillable=[
    "from",
    "from_code",
    "dis",
    "to",
    "to_code",
 ];



}
