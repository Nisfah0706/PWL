<?php
namespace App\Controllers;

class ProfileController extends BaseController
{
    public function index(){
        $data = [
            'username' => session()->get('username'),
            'role' => session()->get('role'),
            'email' => session()->get('email'),
            'login_time' => session()->get('login_time') ?? date('Y-m-d H:i:s'),
            'status' => session()->get('isLoggedIn'),
        ];
        return view('v_profile', $data);
    }
}