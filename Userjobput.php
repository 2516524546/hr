<?php
namespace app\index\controller;

use think\Controller;
use think\Session;
class Userjobput extends Base
{
    public function userjobput()
    {   $type_id = Session::get('type_id');
    // halt($type_id);
    $this->assign([
        'type_id' => $type_id
    ]);
        return $this->fetch('');
    }
}
