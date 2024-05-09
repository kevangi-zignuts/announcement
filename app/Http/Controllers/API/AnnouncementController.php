<?php

namespace App\Http\Controllers\API;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class AnnouncementController extends Controller
{
    /**
     * show a listing of all the Annousements
     */
    public function index(Request $request){
        $request->validate([
            'filter'      => 'nullable|string|in:past,upcoming',
            'per_page'    => 'nullable|integer|min:1|required_with:page_number',
            'page_number' => 'nullable|integer|min:1|required_with:per_page',
        ], [
            'filter.in' => 'The filter must be one of: past, upcoming.',
        ]);
        $perPage    = $request->input('per_page', 10);
        $pageNumber = $request->input('page_number', 1);
        $now        = Carbon::now();
        $query      = Announcement::query();

        $query->where(function($query) use ($request, $now){
            if ($request->input('filter') && $request->input('filter') == 'past') {
                $query->whereRaw("CONCAT(date, ' ', time) < '{$now->toDateTimeString()}'");
            }else if($request->input('filter') && $request->input('filter') == 'upcoming'){
                $query->whereRaw("CONCAT(date, ' ', time) > '{$now->toDateTimeString()}'");
            }
        });
        
        $announcements = $query->paginate($perPage, ['*'], 'page', $pageNumber);
        
        return response()->json($announcements);
    }

    /**
     * store a data of new Added announcement
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'message' => 'required|string|max:64',
            'date'    => 'required|date_format:Y-m-d',
            'time'    => 'required|date_format:H:i',
        ]);
        Announcement::create($data);

        return response()->json($data, 201);
    }

    /**
     * display a View Page of Particular announcement
     */
    public function view($id)
    {
        $announcement = Announcement::findOrFail($id);

        return response()->json($announcement, 200);
    }

    /**
     * update the annousement details
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:64',
            'date'    => 'required|date_format:Y-m-d',
            'time'    => 'required|date_format:H:i',
        ]);
        
        $announcement = Announcement::findOrFail($id);
        
        // past announcement can't be update validation
        $validator->after(function ($validator) use ($announcement) {
            if (strtotime($announcement->date . ' ' . $announcement->time) <= Carbon::now()->timestamp) {
                $validator->errors()->add('datetime', 'The announcement datetime are in past so announcement can not be updated');
            }
        });
        
        if($validator->fails()){
            return response()->json($validator->errors()->first(), 422);
        }

        $announcement->update($request->only(['message', 'date', 'time']));

        return response()->json($announcement, 200);
    }

    /**
     * delete the announcement data
     */
    public function delete(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'forceDelete' => 'nullable|boolean'
        ]);
        $announcement = Announcement::findOrFail($id);
        
        // past announcement can't be delete validation
        $validator->after(function ($validator) use ($announcement) {
            if (strtotime($announcement->date . ' ' . $announcement->time) <= Carbon::now()->timestamp) {
                $validator->errors()->add('datetime', 'The announcement datetime are in past so announcement can not be deleted');
            }
        });

        if($validator->fails()){
            return response()->json($validator->errors()->first(), 422);
        }
        // If request has forceDelete and it is true then announcement data are hard deleted
        if ($request->has('forceDelete') && $request->input('forceDelete') === "1") {
            $announcement->forceDelete();
            return response()->json([
                'status'  => 'success',
                'message' => "Announcement's data deleted Successfully", 
            ], 200);
        }
        $announcement->delete();
        return response()->json([
            'status'  => 'success',
            'message' => "Announcement's data deleted Successfully", 
        ], 200);
    }
}
