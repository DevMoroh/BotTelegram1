<?php


namespace BotTelegram\Controllers;
use BotTelegram\bot\BotTelegram;
use BotTelegram\Models\Notifications;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Symfony\Component\Console\Input\Input;

class NotificationsController extends Controller{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $notifications = Notifications::all();
        return response()->json($notifications->toArray());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $valid = Validator::make($request->input(), Notifications::$validate['rules'], Notifications::$validate['messages']);

        if ($valid->fails())
        {
            $result = $valid->messages();
            return response()->json($result, 422);
        }else{
            $result = Notifications::create($request->input());
            return response()->json($result);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $notification = Notifications::with('files')->find($id);
        if($notification) {
            return response()->json($notification);
        }else{
            //Exception
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $input = $request->input();
        $valid = Validator::make($request->input(), Notifications::$validate['rules'], Notifications::$validate['messages']);

        if ($valid->fails())
        {
            $result = $valid->messages();
            return response()->json($result, 422);
        }else{
            $command = Notifications::find($id);
            unset($input['id']);
            $command->update($input);
            $result = $command->save();
            
            return response()->json($command->toArray());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $result = Notifications::destroy($id);
        return response()->json($result);

    }


}