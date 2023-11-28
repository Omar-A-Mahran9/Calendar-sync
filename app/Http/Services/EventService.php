<?php
namespace App\Http\Services;

use App\Models\Event;
use Carbon\Carbon;
 
class EventService{
    protected $user;
    public function __construct($user)
    {
        $this->user=$user;
    }
    public function create($data){
        // If(isset($data['is_all_day']) && $data['is_all_day']==1){
        //     $date_diff=Carbon::createFromTimestamp(strtotime($data['end']))->diffInDays(Carbon::createFromTimestamp(strtotime($data['start'])));
        //     $data['end']=Carbon::createFromTimestamp(strtotime($data['start']))->addDays($date_diff)->toDateString();
        // }
        $event=new Event($data);
        $event->save();
        // SyncEventWithGoogle::dispatch($event,$this->user);
        return $event;
    }
    public function update($id,$data){
    
        $event=Event::find($id);
        $event->fill($data);
        $event->save();
        // SyncEventWithGoogle::dispatch($event,$this->user);

        return $event;
    }
public function allEvents ($filters){
$eventQuery= Event::query();
 $eventQuery->where ('user_id',$this->user->id);
if($filters['start']){
    $eventQuery = $eventQuery->where('start','>=',$filters['start'] );
}
if($filters['end']){
    $eventQuery = $eventQuery->where('end','<=',$filters['end'] );
}
$events=$eventQuery->get();
$data=[];
foreach($events as $event){
    if(!(int)$event['is_all_day']){
        $event['allDay']=false;
        $event['start']=Carbon::createFromTimestamp(strtotime($event['start']))->toDateTimeString();
        $event['end']=Carbon::createFromTimestamp(strtotime($event['end']))->toDateTimeString();
        $event['endDay']=$event['end'];
        $event['startDay']=$event['start'];
    }
    else{
        $event['allDay']=false;
        $event['endDay']=Carbon::createFromTimestamp(strtotime($event['end']))->addDays(-1)->toDateTimeString();;
        $event['startDay']=$event['start'];
    }
    $event['eventid']=$event['id'];
    array_push($data,$event);
}
 return $data;
}
}