<?php
namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Session;
class Base extends Controller{

    //初始化判断是否登录
    public function _initialize(){
        if(session('id') != null){
            //查询该session值是否还存在
            $has_this = Db::table('user')->where('id',session('id'))->find();
            if($has_this){
                //直接返回
                return;
            }else{
                //否则清空session
                session('type_id',null);
                session('id',null);
                $this->redirect('register/register');
            }
        }
    }

    //共用退出登录方法
    public function logout(){
        session('id',null);
        session('type_id',null);
        $type_id = session('type_id');
        $uid = session('id');
        //判断session是否删除
        if($type_id == null && $uid == null){
            //返回成功信息
            return $this->result('',1,'退出成功');
        }else{
            //返回失败信息
            return $this->result('',0,'退出登录失败');
        }
    }

}
