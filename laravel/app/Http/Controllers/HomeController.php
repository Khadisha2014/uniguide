<?php

namespace App\Http\Controllers;

use App\Models\University;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            'universities' => University::query()->where('is_published', true)->orderBy('world_rank')->get(),
            'countries' => University::query()->where('is_published', true)->distinct()->orderBy('country')->pluck('country'),
        ]);
    }
}
