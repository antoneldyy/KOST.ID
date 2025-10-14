<?php

namespace App\Http\Controllers;

use App\Models\Activity;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = Activity::with('user')->latest()->paginate(20);
        return view('activities.index', compact('activities'));
    }
}


