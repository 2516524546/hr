<?php
namespace app\index\controller;

use think\Controller;
use think\Session;
class Companydashboard extends Controller
{
    public function companydashboard()
    {
        $pic = Session::get('pic');
        $userid =session('id');
        if (empty($userid)) {
            return $this->error('你还未登录',"/index/login/login");
        }
        $type_id = Session::get('type_id');
        // halt($type_id);
        $this->assign([
            'type_id' => $type_id
        ]);
        return $this->fetch('');
    }
}
