<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Travel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class TravelController extends Controller
{

    public function index()
    {
        $travels = Travel::all();
        return response()->json($travels, 200);
    }

    public function show($slug)
    {
        $travel = Travel::where('slug', $slug)->first();
        return response()->json($travel, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'meal' => 'nullable|string',
            'curiosity' => 'nullable|string',
        ]);

        $slug = $this->generateUniqueSlug($request->name);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');
        }

        $travel = Travel::create([
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'image' => $imagePath,
            'meal' => $request->meal,
            'curiosity' => $request->curiosity,
            'slug' => $slug,
        ]);

        return response()->json($travel, 201);
    }

    public function update(Request $request, $slug)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'image' => 'nullable|string',
            'meal' => 'nullable|string',
            'curiosity' => 'nullable|string',
        ]);

        $travel = Travel::where('slug', $slug)->firstOrFail();

        if ($request->name !== $travel->name) {
            $slug = $this->generateUniqueSlug($request->name);
        } else {
            $slug = $travel->slug;
        }

        if ($request->image) {
            // Decode the base64 image
            $imageData = $request->image;
            list($type, $data) = explode(';', $imageData);
            list(, $data) = explode(',', $data);
            $data = base64_decode($data);

            // Determine the image type
            if (strpos($type, 'jpeg') !== false) {
                $extension = 'jpg';
            } elseif (strpos($type, 'png') !== false) {
                $extension = 'png';
            } elseif (strpos($type, 'gif') !== false) {
                $extension = 'gif';
            } else {
                return response()->json(['error' => 'Invalid image type'], 422);
            }

            // Generate a unique file name and store the image
            $fileName = Str::random() . '.' . $extension;
            $filePath = 'images/' . $fileName;
            Storage::disk('public')->put($filePath, $data);

            // Log the image path
            \Log::info('Image saved to: ' . $filePath);

            // Delete the old image if it exists
            if ($travel->image) {
                Storage::disk('public')->delete($travel->image);
            }

            $travel->image = $filePath;
        }

        $travel->update([
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'meal' => $request->meal,
            'curiosity' => $request->curiosity,
            'slug' => $slug,
        ]);

        return response()->json($travel, 200);
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


