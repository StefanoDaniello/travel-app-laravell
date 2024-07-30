<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Travel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TravelController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'image' => 'nullable|string',
            'meal' => 'nullable|string',
            'curiosity' => 'nullable|string',
        ]);

        $slug = $this->generateUniqueSlug($request->name);

        $travel = Travel::create([
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'image' => $request->image,
            'meal' => $request->meal,
            'curiosity' => $request->curiosity,
            'slug' => $slug,
        ]);

        return response()->json($travel, 201);
    }

    private function generateUniqueSlug($name)
    {
        $slug = Str::slug($name, '-');
        $originalSlug = $slug;
        $count = 1;

        while (Travel::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }
}


