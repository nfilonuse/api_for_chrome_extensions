<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class SiteController extends Controller
{
    public function maintenance(Request $requset)
    {
        return view('home.maintenance');
    }
}
