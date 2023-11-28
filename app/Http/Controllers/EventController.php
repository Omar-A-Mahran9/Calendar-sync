<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Services\EventService;
use Carbon\Carbon;
// use App\Models\Event;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;

use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    return view('events.list');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateEventRequest $request)
    {
        $data=$request->all();
        $data['user_id']=auth()->user()->id;
        $eventService=new EventService(auth()->user());
        $event=$eventService->create($data);
         if ($event){
            
            $client = new Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        $client->setAccessToken(auth()->user()->gcalendar_access_token);
        $client->setScopes([
            'https://www.googleapis.com/auth/calendar',
         ]);
        $service = new Calendar($client);
         $events = new Event([
            'summary' =>  $request->title,
            'location' => 'Event Location',
            'description' => $request->description,
            'start' => [
                'dateTime' => Carbon::createFromTimestamp(strtotime($request->start))->toDateTimeString(),
                'timeZone' => 'UTC', // Set the timezone accordingly
            ],
            'end' => [
                'dateTime' => Carbon::createFromTimestamp(strtotime($request->end))->toDateTimeString(),
                'timeZone' => 'UTC', // Set the timezone accordingly
            ],
        ]);
        $calendarId = 'primary';
 
        try {
          $result= $service->events->insert($calendarId, $events);
           return redirect()->back()->with('success', 'Event added to Google Calendar successfully!');
             // Handle success
        } catch (\Exception $e) {
            // Log or handle the exception
            dd($e->getMessage());
        }
        // return response()->json([
        //         'success'=>'true',
        //     ]);
        }else{
            return response()->json([
                'success'=>'false ',
            ]); 
        }
    }
    public function refetchEvents(Request $request)
    {
         
        $eventService=new EventService(auth()->user());
        $eventsData=$eventService->allEvents($request->all());
        return response()->json($eventsData);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, $id)
    {
        $data=$request->all();
        $eventService=new EventService(auth()->user());
        $event=$eventService->update($id,$data);
        if ($event){
            return response()->json([
                'success'=>'true',
            ]);
        }else{
            return response()->json([
                'success'=>'false ',
            ]); 
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(\App\Models\Event $event)
    {
      try{
    // if($event->event_id){
    //         $eventService=new EventService(auth()->user());
    //         $eventService->syncWithGoogle($event,true);
    //     }
        $event->delete();
        return response()->json(['success'=>true]);
      } catch(\Exception $exception){
        return response()->json(['success'=>true]);
      }
    }
}
