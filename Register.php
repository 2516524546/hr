<?php
namespace app\index\controller;

use think\Controller;
use think\Session;
use Ali\SmsDemo;

class Register extends Controller
{
    public function register()
    {
        return $this->fetch();
    }

    public function register_now(){
        if(!request()->isAjax()){
            return $this->result('',0,'非法参数错误');
        }else{
            $data = input('post.');
            // halt($data);
            if($data['code'] != session('SMS.code') || $data['tel'] != session('SMS.phone')){
                return $this->result('',0,'验证码错误');
            }else{
                $user = model('User')->get(['tel'=>$data['tel']]);
                if(!empty($user)){
                    return $this->result('',0,'该手机号码已经注册');
                }else{
                    $data['password'] = md5($data['password']);
                    //判断是用户还是企业按钮下的注册
                    $res = model('User')->add($data);
                }
                if($res){
                    return $this->result('',1,'注册成功');
                }else{
                    return $this->result('',0,'注册失败');
                }
            }
        }
    }


    public function register_forget(){
        if(!request()->isAjax()){
            return $this->result('',0,'非法参数错误');
        }else{
            $data = input('post.');
            //halt($data);
            if($data['code'] != session('SMS.code') || $data['tel'] != session('SMS.phone')){
                return $this->result('',0,'验证码错误');
            }else{
                $user = model('User')->get(['tel'=>$data['tel']]);
                if(empty($user)){
                    return $this->result('',0,'该手机号码不存在');
                }else{
                    $data['password'] = md5($data['password']);
                    $res = model('User')->allowField(true)->save($data,['tel'=>$data['tel']]);
                }
                if($res){
                    return $this->result('',1,'修改成功');
                }else{
                    return $this->result('',0,'修改失败');
                }
            }
        }
    }


    //
    public function verification(){
        //验证部分数据合法性
        if (request()->isPost()){
            $Ali = new SmsDemo('AccessKeyId', 'AccessKeySecret');
            $phone = input('post.phone');                //发送的手机号码
            $type_id = input('post.type_id');           //用来判断企业还是用户注册
            if($type_id == 1){
              $tel = model('User')->get(['tel'=>$phone]);
            }else{
              $tel = model('Userenterprise')->get(['tel'=>$phone]);
            }
            if(!empty($tel)){
                return $this->result('',0,'该手机号已经被注册过');
            }
            $code = $this->randStr('4','1');     //短信验证码，4位类型1位数字
            $res = $Ali->sendSms($phone,$code);
            if ($res){
                //成功则存入session
                session('SMS',['phone'=>$phone,'code'=>$code,'time'=>time()]);
                return $this->result('',1,'短信已发送，请注意查收！');
            }else{
                return $this->result('',0,'短信发送失败！');
            }
        }
    }

    //验证码验证
    public function verification_code(){
        if (!request()->isAjax()){
            return $this->result('',0,'非法请求！');
        }else{
            //halt(session('SMS'));
            $phone = input('post.phone');
            $code = input('post.code');
            if ($code != session('SMS.code') || $phone != session('SMS.phone')) {
                return $this->result('',0,'验证码错误！');
            } else {
                return $this->result('',1,'验证码正确！');
                //下面是验证正确的业务逻辑
            }
        }
    }
        /**
         * 获取随机数  (验证码随机数)
         * @return string
         */
        public function randStr($length=4,$type="1"){
            $array = array(
                '1' => '0123456789',
                '2' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                '3' => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
            );
            $string = $array[$type];
            $count = strlen($string)-1;
            $rand = '';
            for ($i = 0; $i < $length; $i++) {
                $rand .= $string[mt_rand(0, $count)];
            }
            return $rand;
        }

}
