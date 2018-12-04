<?php
namespace app\index\controller;

use think\Controller;
use app\index\model\Tree;
use app\index\model\Attachment;
use think\Session;
class Companydashpro extends Base
{
    public function companydashpro()
    {
        $loginname = Session::get('name');

        $type_id = Session::get('type_id');
        $pic = Session::get('pic');
        $userid =session('id');
        if (empty($userid)) {
            return $this->error('你还未登录',"/index/login/login");
        }
        // halt($type_id);

        return $this->fetch('',[
            'type_id' => $type_id,
            'pic'    => $pic,
            'loginname' => $loginname
        ]);
    }

    //企业注册后的公司填写详细信息页
    public function companydashdetail(){
        $pic = Session::get('pic');
        $type_id = Session::get('type_id');
        // halt($type_id);
        $this->assign([
            'type_id' => $type_id
        ]);
        $model = new Tree();
        $imgmodel = new Attachment();
        $fromid = 1;
        $toid = 1;
        $datetime = time();
        $datetime= intval(date('Y',$datetime));//获取年份，为前端下拉年份做准备

        $province = $model ->where ( array('pid'=>1) )->select ();//筛选第一级三级联动菜单的数据
        return $this->fetch('',[
            'fromid' => $fromid,
            'toid' => $toid,
            'province' => $province,
             'datetime' => $datetime,
             'pic'    => $pic
        ]);
        return $this->fetch();
    }
}
