<?php


namespace BotTelegram\Controllers;
use App\User;
use BotTelegram\bot\BotTelegram;
use BotTelegram\Models\UserService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\Console\Input\Input;

class UserServiceController extends Controller{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {

//        $role_admin = Role::create(['name' => 'admin']);
//        $role_manager = Role::create(['name' => 'manager']);
//
//        $permission_manager = Permission::create(['name' => 'manager bots']);
//        $permission_admin = Permission::create(['name' => 'admin bots']);
//
//        $role_manager->givePermissionTo('manager bots');
//        $role_admin->givePermissionTo('admin bots');
//
//
//        $user = User::find(2);
//        $user->givePermissionTo('admin bots');

        $users = UserService::all();
        return response()->json($users);
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

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $result = UserService::destroy($id);
        return response()->json($result);

    }


}