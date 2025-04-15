<?php

namespace App\Http\Controllers;

use App\Models\TravelPackage;
use Illuminate\Http\Request;

class HolidayspackageController extends Controller
{
    //

    public function SearchHolidayspackage(string $name)
    {

        $package = TravelPackage::where("package_name", "LIKE", "%{$name}%")
        ->orWhere("package_Type", "LIKE", "%{$name}%")
        ->orWhere("city", "LIKE", "%{$name}%")->where("is_active",true)->get();


        if(!$package){
         return response()->json(["success"=>false,"message"=>"package not found"]);
        }
   



        return $package;
    }
    public function GetHolidayPackage(string $slug){
        $package = TravelPackage::where("slug",$slug)->first();
        
        return $package ;


    }

public  function getActivePackage(){
    $package=TravelPackage::where("is_active",true)->get();

    return $package;
}



}
