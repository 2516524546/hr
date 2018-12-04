<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/9
 * Time: 15:17
 */
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Session;
use app\index\model\User;
use app\index\model\guestChat;

class Customer extends Base
{


    //聊天页
    public function customer()
    {
        $loginname = Session::get('name');

        $pic = Session::get('pic');
        $type_id = Session::get('type_id');
        // halt($type_id);
        $this->assign([
            'type_id' => $type_id
        ]);
        $usermodel = new User();

        //拿取用户id使用三元运算符判断
        $useruid = $this->request->has('useruid') ? $this->request->param('useruid', 0, 'intval') : 0;
        //拿取session数值
        $fromid = session('id');
        // halt($type_id);
        if (empty($fromid)) {
            return $this->error('你还未登录',"/index/login/login");
        }
        //获取toid发送者id
        $toid = $useruid;
        //查询自己头像
        $userimgme =$usermodel->where('id' ,$fromid)->field('pic')->find();
        //查询该用户头像
        $userimg =$usermodel->where('id' , $toid)->field('pic')->find();
        $username =$usermodel->where('id' , $toid)->field('name')->find();

        //拿取数值头像下标pic
        $userimg= $userimg['pic'];

        $userimgme=$userimgme['pic'];
        //返回模板数据并输出模板
        return $this->fetch('',[
            'fromid' => $fromid ,
            'toid' => $toid,
            'userimg' =>$userimg,
            'username' =>$username,
            'userimgme'=>$userimgme,
            'pic'    => $pic,
            'loginname' => $loginname
        ]);
    }

    //用户
    public function customer_user()
    {

        //利用三元运算符判断type_id
        $type_id = $this->request->has('type_id') ? $this->request->param('type_id', 0, 'intval') : 0;
        //如果存在uid就拿uid否则取0
        $uid =  $this->request->has('uid') ? $this->request->param('uid', 0, 'intval') : 0;
        //如果type_id为999，拿取session的id，
        if ($type_id == 999){
            $fromid = session('id');
            //并toid等于uid
            $toid = $uid ;
        }

        //返回值并渲染模板
        return $this->fetch('',[
            'fromid' => $fromid ,'toid' => $toid
        ]);

    }


    /**
     *修改未读消息成已读
     */
    public function changeNoRead(){

        $fromid = input('fromid');
        $toid = input('toid');
        $res = Db::table('guest_chat')->where(['fromid'=>$toid,'toid'=>$fromid])->update(['isread'=>1]);
        return $res;

    }

    /**
     * 查询用户头像ajax
     */
    public function get_head(){
        $usermodel = new User();
        //判断是不是ajax请求
        if(request()->isAjax()){
            //获取fromid
            $fromid = input('post.fromid');
            //查询头像
            $toinfo = $usermodel->where('id',$fromid)->field('pic')->find();
            //返回头像信息
            return $toinfo['pic'];
        }
    }

    /**
     * 聊天记录写入数据库
     */
    public function save_message(){
        //判断是不是ajax请求

         $chatmodel = new guestChat();
         if(request()->isAjax()){
            //获取post过来的数据
            $message=  $this->request->post();

            //获取fromid
            $datas['fromid']=$message['fromid'];
            //通过getName方法获取名字
            $datas['fromname']= $this->getName($datas['fromid']);
            //赋值toid
            $datas['toid']=$message['toid'];
            //获取toname的toname为找找客服
            $datas['toname']= $this->getName($datas['toid']);
            //获取data下标数据
            $datas['content']=$message['data'];
            //获取时间
            $datas['time']=time();
            //默认未读
            $datas['isread']=0;
            //默认type_id为1
            $datas['type'] = 1;
            //插入数据库
            $res= $chatmodel->insert($datas);
            if ($res) {
                $res =  $chatmodel->where(['toid'=>$message['fromid']])->where(['fromid'=>$message['toid']])->find();

               if (empty($res)) {
                   $post['fromid']=$message['toid'];
                   $post['toid']=$message['fromid'];
                   $post['fromname']= $this->getName($datas['toid']);
                   $post['toname']= $this->getName($datas['fromid']);
                   //获取data下标数据
                   $post['content']="hi!";
                       //获取时间
                   $post['time']=time();

                   //插入数据库
                   $chatmodel->allowField(true)->save($post);

               }
            }

        }
    }

    /**
     * 图片上传，返回路径名
     */
    public function uploadimg(){
        $chatmodel = new guestChat();
        $file = $_FILES['file'];
        $fromid = input('post.fromid');
        $toid = input('post.toid');

        $suffix = strtolower(strrchr($file['name'],'.'));    //获取文件后缀
        $type = ['.jpg','.jpeg','.gif','.png'];
        if(!in_array($suffix,$type)){
            return ['status'=>0,'msg'=>'图片格式不正确'];
        }
        //判断图片格式是否大于5120kb
        if ($file['size']/1024>5120){
            return ['status'=>0,'msg'=>'图片过大'];
        }
        //实例化图片类
        $up = new Image();
        //上传到七云牛
        $name = $up->upload_img();
        //获取内容
        $data['content'] = $name;
        //获取fromid
        $data['fromid'] = $fromid;
        //获取toid
        $data['toid'] = $toid;
        //方法获取名字
        $data['fromname'] = $this->getName($fromid);
        //赋值toname为“找找客服”
        $data['toname'] = $this->getName($toid);
        //默认未读
        $data['isread'] = 0;
        //时间
        $data['time'] = time();
        //赋值type为2
        $data['type'] = 2;
        //插入数据后获取ID
        $message_id = $chatmodel ->insertGetId($data);
        //格式化时间内容
        $time = date("Y-m-d H:i:s", $data['time']);
        if ($message_id){
            //返回图片路径
            return $this->result(['img_name'=>$name,'time'=>$time],1,'OK') ;
        }else{
            //返回失败信息
            return $this->result('',0,'图片发送失败') ;
        }
    }

    /**
     * 页面加载返回聊天记录
     */
    public function get_load(){
        $chatmodel = new guestChat();
        //判断是不是ajax请求
        if(request()->isAjax()){
            //获取fromid
            $fromid = input('post.fromid');
            //获取toid
            $toid = input('post.toid');
            //得到count数据
            $count =  $chatmodel->where('(fromid=:fromid and toid=:toid) || (fromid=:toid1 and toid=:fromid1)',['fromid'=>$fromid,'toid'=>$toid,'toid1'=>$toid,'fromid1'=>$fromid])->count('id');
            //如果count超过20，
            if($count>=20){
                //显示最新的20条
                $message = $chatmodel->where('(fromid=:fromid and toid=:toid) || (fromid=:toid1 and toid=:fromid1)',['fromid'=>$fromid,'toid'=>$toid,'toid1'=>$toid,'fromid1'=>$fromid])->order('id')->select();
            }else{
                //显示20条
                $message =   $chatmodel->where('(fromid=:fromid and toid=:toid) || (fromid=:toid1 and toid=:fromid1)',['fromid'=>$fromid,'toid'=>$toid,'toid1'=>$toid,'fromid1'=>$fromid])->order('id')->select();
            }
            //halt($this->to_headimg2($toid));
            return $message;
        }
    }

    /**
     * 根据用户id查询用户昵称
     */
    public function getName($uid){
        $usermodel = new User();
        $userinfo = $usermodel-> where('id',$uid)->field('name')->find();
        return $userinfo['name'];
    }


    //----------------------------------------------------







    //每个用户的删除方法，彼此不影响
    public function del()
    {
        //拿取session的当前用户id
        $uid = session('id');
        //判断是不是ajax请求
        if(!request()->isAjax()){
            //返回非法参数错误
            return $this->result('',0,'非法参数错误');
        }else{
            //拿取全部的data变量
            $data = input('post.');
            //赋值用户uid
            $data['uid'] = $uid;
            //将删除信息插入数据库
            $mess_del = db('del_message')->insert($data);
            if($mess_del){
                //返回已移除
                return $this->result('',1,'已移除');
            }else{
                //返回未知错误
                return $this->result('',0,'未知错误');
            }
        }
    }


}
