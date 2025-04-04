<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Popular_Destination;
use App\Models\Popular_hotels;

class Popular_destController extends Controller
{
    //
    public function Popular_flight(){
      $info=  Popular_Destination::all();
        return $info ;
    }
    public function Popular_hotel(){
        $info=  Popular_hotels::all();
          return $info ;
      }
}
