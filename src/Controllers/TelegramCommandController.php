<?php


namespace BotTelegram\Controllers;
use BotTelegram\bot\BotTelegram;
use BotTelegram\Models\CommandsTelegram;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Symfony\Component\Console\Input\Input;

class TelegramCommandController extends Controller{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $commands = CommandsTelegram::with('subcommands')->get();
        return response()->json($commands);
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
        $valid = Validator::make($request->input(), [
            'name' => 'required',
            'type' => 'required',
            'message' => 'required',
        ],
        [
            'name.required' => 'Заполните поле Имя',
            'type.required' => 'Заполните поле Тип',
            'message.required' => 'Заполните поле Сообщения',
        ]);

        if ($valid->fails())
        {
            $result = $valid->messages();
            return response()->json($result, 422);
        }else{
            $cm = new CommandsTelegram;
            $input = $request->input();
            $cm->name = $input['name'];
            $cm->message = $input['message'];
            $cm->status = $input['status'];
            $cm->type = $input['type'];
            $result = $cm->save();
            $cm->tags_list = ($input['tags_list']) ? $input['tags_list'] : "";

          //  $result = CommandsTelegram::create($request->input());
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
    public function update($id, Request $request)
    {
        $input = $request->input();

        $valid = Validator::make($request->input(), CommandsTelegram::$validate['rules'], CommandsTelegram::$validate['messages']);

        if ($valid->fails())
        {
            $result = $valid->messages();
            return response()->json($result, 422);
        }else{
            $command = CommandsTelegram::find($id);
            $result = $command->update($input);
            $command->save();
            $command->tags_list = $input['tags_list'];
            return response()->json($result);
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
        $result = CommandsTelegram::destroy($id);
        return response()->json($result);

    }


}