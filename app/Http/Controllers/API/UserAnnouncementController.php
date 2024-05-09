<?php

namespace App\Http\Controllers\API;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class UserAnnouncementController extends Controller
{
    /**
     * show a listing of all the Announcements
     */
    public function index(Request $request)
    {
        $now = Carbon::now();
        $perPage = $request->input('per_page', 10);
        $announcements = Announcement::query()->where(function ($query) use ($request, $now) {
            if ($request->input('filter') && $request->input('filter') == 'past') {
                $query->whereRaw("CONCAT(date, ' ', time) < '{$now->toDateTimeString()}'");
            }else if($request->input('filter') && $request->input('filter') == 'upcoming'){
                $query->whereRaw("CONCAT(date, ' ', time) > '{$now->toDateTimeString()}'");
            }
        })->paginate($perPage);
        
        return response()->json(['announcements' => $announcements]);
    }

    /**
     * show a View Page of Particular annousement
     */
    public function view($id)
    {
        $announcement = Announcement::findOrFail($id);
        if($announcement->status == 'N'){
            $announcement->update(['status' => 'V']);
        }

        return response()->json($announcement, 200);
    }
}
