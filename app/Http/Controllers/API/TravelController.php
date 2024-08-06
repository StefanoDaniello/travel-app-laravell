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
        'roads' => 'required|array',
        'roads.*.name' => 'required|string|max:255',
        'roads.*.description' => 'nullable|string',
        'roads.*.start_date' => 'required|date',
        'roads.*.end_date' => 'required|date',
        'roads.*.rate' => 'nullable|integer',
        'roads.*.note' => 'nullable|string',
        'roads.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
    ]);

    $imagePath = null;
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('images', 'public');
    }

    // Create the travel record
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
    ]);

    // Iterate over the roads and create records for each
    foreach ($request->roads as $roadData) {
        $roadImagePath = null;
        if (isset($roadData['image']) && $roadData['image']) {
            $roadImage = $roadData['image'];
            $roadImagePath = $roadImage->store('images', 'public');
        }

        $roadSlug = $this->generateUniqueSlug($roadData['name']);
        Road::create([
            'name' => $roadData['name'],
            'description' => $roadData['description'],
            'image' => $roadImagePath,
            'start_date' => $roadData['start_date'],
            'end_date' => $roadData['end_date'],
            'rate' => $roadData['rate'],
            'note' => $roadData['note'],
            'slug' => $roadSlug,
            'travel_id' => $travel->id, // Associa la road al travel
        ]);
    }

    return response()->json($travel, 201);
}


public function update(Request $request, $slug){

    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'start_date' => 'required|date',
        'end_date' => 'required|date',
        'image' => 'nullable|max:2048',
        'meal' => 'nullable|string',
        'curiosity' => 'nullable|string',
        'road' => 'required|array',
        'road.*.name' => 'required|string|max:255',
        'road.*.description' => 'nullable|string',
        'road.*.start_date' => 'required|date',
        'road.*.end_date' => 'required|date',
        'road.*.rate' => 'nullable|integer',
        'road.*.note' => 'nullable|string',
        'road.*.image' => 'nullable|max:2048'
    ]);

    $travel = Travel::where('slug', $slug)->firstOrFail();	
        $travel->update([
        'name' => $request->name,
        'description' => $request->description,
        'image' => $request->image,
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'meal' => $request->meal,
        'curiosity' => $request->curiosity,
        'slug' => $request->slug,
    ]);



    // Iterate over the road and create records for each
    foreach ($request->road as $roadData) {
        $road = Road::where('slug', $roadData['slug'])->firstOrFail();
        $road->update([
            'name' => $roadData['name'],
            'description' => $roadData['description'],
            'image' => $roadData['image'],
            'start_date' => $roadData['start_date'],
            'end_date' => $roadData['end_date'],
            'rate' => $roadData['rate'],
            'note' => $roadData['note'],
            'slug' => $roadData['slug'],
        ]);
        
    }

    return response()->json(['travel' => $travel, 'road' => $travel->road], 200);
}

// public function update(Request $request, $slug)
// {
//     $request->validate([
//         'name' => 'required|string|max:255',
//         'description' => 'nullable|string',
//         'start_date' => 'required|date',
//         'end_date' => 'required|date',
//         'image' => 'nullable|string',
//         'meal' => 'nullable|string',
//         'curiosity' => 'nullable|string',
//         'roads' => 'required|array',
//         'roads.*.name' => 'required|string|max:255',
//         'roads.*.description' => 'nullable|string',
//         'roads.*.start_date' => 'required|date',
//         'roads.*.end_date' => 'required|date',
//         'roads.*.rate' => 'required|integer',
//         'roads.*.note' => 'nullable|string',
//         'roads.*.image' => 'nullable|string'
//     ]);

//     $travel = Travel::where('slug', $slug)->firstOrFail();

//     if ($request->name !== $travel->name) {
//         $travelSlug = $this->generateUniqueSlug($request->name);
//     } else {
//         $travelSlug = $travel->slug;
//     }

//     if ($request->image) {
//         $imageData = $request->image;
//         if (strpos($imageData, ';base64,') !== false) {
//             list($type, $data) = explode(';base64,', $imageData);
//             $data = base64_decode($data);

//             if (strpos($type, 'image/jpeg') !== false) {
//                 $extension = 'jpg';
//             } elseif (strpos($type, 'image/png') !== false) {
//                 $extension = 'png';
//             } elseif (strpos($type, 'image/gif') !== false) {
//                 $extension = 'gif';
//             } else {
//                 return response()->json(['error' => 'Invalid image type'], 422);
//             }

//             $fileName = Str::random() . '.' . $extension;
//             $filePath = 'images/' . $fileName;
//             Storage::disk('public')->put($filePath, $data);

//             \Log::info('Image saved to: ' . $filePath);

//             if ($travel->image) {
//                 Storage::disk('public')->delete($travel->image);
//             }

//             $travel->image = $filePath;
//         }
//     }

//     $travel->update([
//         'name' => $request->name,
//         'description' => $request->description,
//         'start_date' => $request->start_date,
//         'end_date' => $request->end_date,
//         'meal' => $request->meal,
//         'curiosity' => $request->curiosity,
//         'slug' => $travelSlug,
//     ]);

//     foreach ($request->roads as $roadData) {
//         $road = Road::find($roadData['id']);

//         if (!$road) {
//             continue; // Skip if the road is not found
//         }

//         if ($roadData['name'] !== $road->name) {
//             $roadSlug = $this->generateUniqueSlug($roadData['name']);
//         } else {
//             $roadSlug = $road->slug;
//         }

//         if ($roadData['image']) {
//             $roadImageData = $roadData['image'];
//             if (strpos($roadImageData, ';base64,') !== false) {
//                 list($roadType, $roadImage) = explode(';base64,', $roadImageData);
//                 $roadImage = base64_decode($roadImage);

//                 if (strpos($roadType, 'image/jpeg') !== false) {
//                     $roadExtension = 'jpg';
//                 } elseif (strpos($roadType, 'image/png') !== false) {
//                     $roadExtension = 'png';
//                 } elseif (strpos($roadType, 'image/gif') !== false) {
//                     $roadExtension = 'gif';
//                 } else {
//                     return response()->json(['error' => 'Invalid image type'], 422);
//                 }

//                 $roadFileName = Str::random() . '.' . $roadExtension;
//                 $roadFilePath = 'images/' . $roadFileName;
//                 Storage::disk('public')->put($roadFilePath, $roadImage);

//                 \Log::info('Road image saved to: ' . $roadFilePath);

//                 if ($road->image) {
//                     Storage::disk('public')->delete($road->image);
//                 }

//                 $road->image = $roadFilePath;
//             }
//         }

//         $road->update([
//             'name' => $roadData['name'],
//             'description' => $roadData['description'],
//             'start_date' => $roadData['start_date'],
//             'end_date' => $roadData['end_date'],
//             'rate' => $roadData['rate'],
//             'note' => $roadData['note'],
//             'slug' => $roadSlug,
//         ]);
//     }

//     return response()->json(['travel' => $travel, 'roads' => $travel->roads], 200);
// }


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



