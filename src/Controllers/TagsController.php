<?php

namespace BotTelegram\Controllers;

use App\Http\Controllers\Controller;

use BotTelegram\Models\TagsModel;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Validator;

class TagsController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $input = request()->input();
        $tags = TagsModel::orderBy('name', 'DESC')->get();

        return response()->json($tags->toArray());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = request()->input();

        $valid = Validator::make(request()->input(), [
            'name' => 'required',
        ],
            [
                'name.required' => 'Заполните поле Имя',
            ]);

        if ($valid->fails())
        {
            $result = $valid->messages();
            return response()->json($result, 422);
        }else{
            $result = TagsModel::create(request()->input());
            return response()->json($result);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $input = request()->input();

        $valid = Validator::make(request()->input(), [
            'name' => 'required',
        ],
        [
            'name.required' => 'Заполните поле Имя',
        ]);


        if ($valid->fails())
        {
            $result = $valid->messages();
            return response()->json($result, 422);
        }else{
            $tag = TagsModel::find($id);
          // var_dump($id);
            $tag->update($input);
            $result = $tag->save();
            return response()->json($result);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = TagsModel::destroy($id);
        return response()->json($result);
    }
}
