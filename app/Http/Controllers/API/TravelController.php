<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Travel;
use App\Models\Road;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class TravelController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->input('user_id'); 
    
        if (!$userId) {
            return response()->json(['message' => 'ID utente mancante'], 400);
        }
    
        $travels = Travel::with('road')
            ->where('user_id', $userId)
            ->orderBy('start_date', 'desc')
            ->get();
    
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
        'image' => 'nullable|image|max:2048',
        'luogo' => 'required|string',
        'latitudine' => 'nullable',
        'longitudine' => 'nullable',
        'meal' => 'nullable|string',
        'curiosity' => 'nullable|string',
        'user_id' => 'required|exists:users,id',
        'roads' => 'required|array',
        'roads.*.name' => 'required|string|max:255',
        'roads.*.description' => 'nullable|string',
        'roads.*.start_date' => 'required|date',
        'roads.*.end_date' => 'required|date',
        'roads.*.rate' => 'nullable|integer',
        'roads.*.note' => 'nullable|string',
        'roads.*.image' => 'nullable|image|max:2048',
        'roads.*.via' => 'required|string',
        'roads.*.latitudine' => 'nullable',
        'roads.*.longitudine' => 'nullable',
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
        'luogo' => $request->luogo,
        'latitudine' => $request->latitudine,
        'longitudine' => $request->longitudine,
        'meal' => $request->meal,
        'curiosity' => $request->curiosity,
        'user_id' => $request->user_id,
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
            'via' => $roadData['via'],
            'latitudine' => $roadData['latitudine'],
            'longitudine' => $roadData['longitudine'],
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
        'luogo' => 'required|string',
        'latitudine' => 'nullable',
        'longitudine' => 'nullable',
        'meal' => 'nullable|string',
        'curiosity' => 'nullable|string',
        'user_id' => 'required|exists:users,id',
        'road' => 'required|array',
        'road.*.name' => 'required|string|max:255',
        'road.*.description' => 'nullable|string',
        'road.*.start_date' => 'required|date',
        'road.*.end_date' => 'required|date',
        'road.*.rate' => 'nullable|integer',
        'road.*.note' => 'nullable|string',
        'road.*.image' => 'nullable|max:2048',
        'roads.*.via' => 'required|string',
        'roads.*.latitudine' => 'nullable',
        'roads.*.longitudine' => 'nullable',
    ]);

    $travel = Travel::where('slug', $slug)->firstOrFail();	
        $travel->update([
        'name' => $request->name,
        'description' => $request->description,
        'image' => $request->image,
        'luogo' => $request->luogo,
        'latitudine' => $request->latitudine,
        'longitudine' => $request->longitudine,
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'meal' => $request->meal,
        'curiosity' => $request->curiosity,
        'user_id' => $request->user_id,
        'slug' => $request->slug,
    ]);



    // itera sulla richiesta per creare  ogni road
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
            'via' => $roadData['via'],
            'latitudine' => $roadData['latitudine'],
            'longitudine' => $roadData['longitudine'],
            'slug' => $roadData['slug'],
        ]);
        
    }

    return response()->json(['travel' => $travel, 'road' => $travel->road], 200);
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

   public function destroy($slug) {
    $travel = Travel::where('slug', $slug)->firstOrFail();
    $travel->delete();
    return response()->json(['message' => 'Travel deleted successfully'], 200);
   }
}



