<?php
namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Session;

class Login extends Base
{
    //登录页
    public function login()
    {
        //获取ID session 如果有则$res=1提示已经登录
        // $uid = session('id');
        // if ($uid) {
        //   $res = 1;
        // }else {
        //   $res = 0;
        // }
        // halt($res);
        return $this->fetch('');
    }
    //登录提交操作
    public function logindo()
    {
        //判断是否AJAX请求
        if (!request()->isAjax()) {
            return $this->result('',0,'非法请求！');
        }else{
            //接收AJAX的post数据
            $data = input('post.');
            // halt($data);
            $data['password'] = md5($data['password']);
            //根据用户点击的按钮查表
            $res = db('user')->where('tel',$data['tel'])->where('password',$data['password'])->where('type_id',$data['type_id'])->find();
            // halt($res);
            //判断是否能查询到数据
            if ($res) {
                //成功的话把id和类型存进session
                Session::set('id',$res['id']);
                Session::set('type_id',$res['type_id']);
                Session::set('pic',$res['pic']);
                Session::set('name',$res['name']);
                //$first_login = Db::table('user')->where('id',$res['id'])->find();
                if($res['type_id'] == 1){
                    if($res['first_login'] == '0'){
                        Db::table('user')->where('id',$res['id'])->update(['first_login' => '1']);
                        return $this->result(url('postjod/postjod'),1,'登陆成功,请先填写简历信息');
                    }else{
                        return $this->result(url('index/index'),1,'登录成功，正在前往主页面');
                    }
                }else{
                    if($res['first_login'] == '0'){
                        Db::table('user')->where('id',$res['id'])->update(['first_login' => '1']);
                        return $this->result(url('getjob/getjob'),1,'登录成功,请先发布一条信息');
                    }else{
                        return $this->result(url('index/index'),1,'登录成功，正在前往主页面');
                    }
                }
                // Session::set('username',$res['username']);
                // Session::set('tel',$res['tel']);
                //return $this->result('',1,'登陆成功');
            }else{
                return $this->result('',0,'用户名或密码错误,请重新登录');
            }
        }
    }

    //登录判断
    public function isLogin(){
        //查看是否ajax请求
        if(!request()->isAjax()){
            return $this->result('',0,'非法参数错误');
        }else{
            //如果session值不为空
            if(session('id') != null){
                //返回正常是还在登录中
                return $this->result('',1,'');
            }else{
                //返回请先登录
                return $this->result(url('login/login'),0,'请先登录');
            }
        }
    }
}
