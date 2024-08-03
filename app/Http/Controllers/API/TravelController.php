<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Travel;
use App\Models\Road;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class TravelController extends Controller
{
    public function index()
{
    $travels = Travel::with('road')->get();
    return response()->json($travels, 200);
}


    public function show($slug)
{
    $travel = Travel::with('road')->where('slug', $slug)->first();
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
            'road_name' => 'required|string|max:255',
            'road_description' => 'nullable|string',
            'road_start_date' => 'required|date',
            'road_end_date' => 'required|date',
            'road_rate' => 'required|integer',
            'road_note' => 'nullable|string',
            'road_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);


        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');
        }

        $roadImagePath = null;
        if ($request->hasFile('road_image')) {
            $roadImagePath = $request->file('road_image')->store('images', 'public');
        }

        // Create the road record
        $roadSlug = $this->generateUniqueSlug($request->road_name);
        $road = Road::create([
            'name' => $request->road_name,
            'description' => $request->road_description,
            'image' => $roadImagePath,
            'start_date' => $request->road_start_date,
            'end_date' => $request->road_end_date,
            'rate' => $request->road_rate,
            'note' => $request->road_note,
            'slug' => $roadSlug,
        ]);

        // Create the travel record and associate it with the road
        $travelSlug = $this->generateUniqueSlug($request->name);
        $travel = Travel::create([
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'image' => $imagePath,
            'meal' => $request->meal,
            'curiosity' => $request->curiosity,
            'slug' => $travelSlug,
            'road_id' => $road->id,
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
            'road.name' => 'required|string|max:255',
            'road.description' => 'nullable|string',
            'road.start_date' => 'required|date',
            'road.end_date' => 'required|date',
            'road.rate' => 'required|integer',
            'road.note' => 'nullable|string',
            'road.image' => 'nullable|string',
            'image' => 'nullable|string',
            'meal' => 'nullable|string',
            'curiosity' => 'nullable|string',
        ]);
    
        $travel = Travel::where('slug', $slug)->firstOrFail();
        $road = Road::find($travel->road_id);
    
        if ($request->name !== $travel->name) {
            $travelSlug = $this->generateUniqueSlug($request->name);
        } else {
            $travelSlug = $travel->slug;
        }
    
        if ($request->road['name'] !== $road->name) {
            $roadSlug = $this->generateUniqueSlug($request->road['name']);
        } else {
            $roadSlug = $road->slug;
        }
    
        if ($request->image) {
            $imageData = $request->image;
            if (strpos($imageData, ';base64,') !== false) {
                list($type, $data) = explode(';base64,', $imageData);
                $data = base64_decode($data);
    
                if (strpos($type, 'image/jpeg') !== false) {
                    $extension = 'jpg';
                } elseif (strpos($type, 'image/png') !== false) {
                    $extension = 'png';
                } elseif (strpos($type, 'image/gif') !== false) {
                    $extension = 'gif';
                } else {
                    return response()->json(['error' => 'Invalid image type'], 422);
                }
    
                $fileName = Str::random() . '.' . $extension;
                $filePath = 'images/' . $fileName;
                Storage::disk('public')->put($filePath, $data);
    
                \Log::info('Image saved to: ' . $filePath);
    
                if ($travel->image) {
                    Storage::disk('public')->delete($travel->image);
                }
    
                $travel->image = $filePath;
            }
        }
    
        if ($request->road['image']) {
            $roadImageData = $request->road['image'];
            if (strpos($roadImageData, ';base64,') !== false) {
                list($roadType, $roadData) = explode(';base64,', $roadImageData);
                $roadData = base64_decode($roadData);
    
                if (strpos($roadType, 'image/jpeg') !== false) {
                    $roadExtension = 'jpg';
                } elseif (strpos($roadType, 'image/png') !== false) {
                    $roadExtension = 'png';
                } elseif (strpos($roadType, 'image/gif') !== false) {
                    $roadExtension = 'gif';
                } else {
                    return response()->json(['error' => 'Invalid image type'], 422);
                }
    
                $roadFileName = Str::random() . '.' . $roadExtension;
                $roadFilePath = 'images/' . $roadFileName;
                Storage::disk('public')->put($roadFilePath, $roadData);
    
                \Log::info('Road image saved to: ' . $roadFilePath);
    
                if ($road->image) {
                    Storage::disk('public')->delete($road->image);
                }
    
                $road->image = $roadFilePath;
            }
        }
    
        $travel->update([
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'meal' => $request->meal,
            'curiosity' => $request->curiosity,
            'slug' => $travelSlug,
        ]);
    
        $road->update([
            'name' => $request->road['name'],
            'description' => $request->road['description'],
            'start_date' => $request->road['start_date'],
            'end_date' => $request->road['end_date'],
            'rate' => $request->road['rate'],
            'note' => $request->road['note'],
            'slug' => $roadSlug,
        ]);
    
        return response()->json(['travel' => $travel, 'road' => $road], 200);
    }
    
    private function generateUniqueSlug($name)
    {
        $slug = Str::slug($name, '-');
        $originalSlug = $slug;
        $count = 1;
    
        while (Travel::where('slug', $slug)->exists() || Road::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }
    
        return $slug;
    }
}



