<?php
namespace app\index\controller;

use think\Controller;
use think\Session;
class Userresume extends Base
{
    public function userresume()
    {   $type_id = Session::get('type_id');
    // halt($type_id);
    $this->assign([
        'type_id' => $type_id
    ]);
        return $this->fetch('');
    }
}
