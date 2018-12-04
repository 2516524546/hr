<?php
namespace app\index\controller;

use think\Controller;
use think\Session;

class Aboutus extends Base{

    //关于我们
    public function aboutus()
    {
        $pic = Session::get('pic');
        $type_id = Session::get('type_id');
        // halt($type_id);
        $this->assign([
            'type_id' => $type_id
        ]);
        return $this->fetch();
    }

    //联系我们
    public function contact()
    {
        $type_id = Session::get('type_id');
        // halt($type_id);
        $this->assign([
            'type_id' => $type_id
        ]);
        return $this->fetch();
    }
}
