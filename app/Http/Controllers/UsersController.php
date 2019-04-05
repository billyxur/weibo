<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;

class UsersController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth', [
            'except' => ['show', 'create', 'store', 'index']
        ]);

        //只让未登录用户访问注册页面：
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    public function index()
    {
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }


    //注册页面
    public function create()
    {
        return view('users.create');
    }


    //个人中心
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }


    //登录处理
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        //用户注册后自动登录
        Auth::login($user);
        session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
        return redirect()->route('users.show',[$user]);
    }


    //编辑个人信息页面
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }


    //更新个人信息函数
    public function update(User $user, Request $request)
    {
         $this->authorize('update', $user);
         $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

         $data = [];
         $data['name'] = $request->name;
         if($request->password){
            $data['password'] = bcrypt($request->password);
         }

         $user->update($data);

         session()->flash('success', '个人资料更新成功！');

         return redirect()->route('users.show', $user->id);
    }
}
