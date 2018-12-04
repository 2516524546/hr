<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/9
 * Time: 17:03
 */
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Session;
use app\index\model\User;
use app\index\model\guestChat;

class Customerlist extends Base
{
    /**
     * 聊天页面
     * @return mixed
     */
    public function customerlist(){

        $loginname = Session::get('name');
        $pic = Session::get('pic');
        $usermodel = new User();
        $type_id = Session::get('type_id');
        // halt($type_id);

        $fromid = session('id');
        if (empty($fromid)) {
            return $this->error('你还未登录',"/index/login/login");
        }
        $toid =  $this->request->has('toid') ? $this->request->param('toid',0,'intval') : $this->get_toid($fromid) ;

         //查询自己头像
        $userimg =  $usermodel->where('id' , $fromid)->field('pic')->find();
        $name = $usermodel->where('id' , $toid)->field('name')->find();
        //查询该用户头像
        $userimgme =$usermodel->where('id' , $toid)->field('pic')->find();

        $res  = Db::table('guest_chat')->where('fromid' , $toid)->update(['isread'=>1]);
        $userimg= $userimg['pic'];
        $userimgme= $userimgme['pic'];
        $name=$name['name'];
        return $this->fetch('',[
            'fromid'=>$fromid,
            'toid'=>$toid,
            'userimg' => $userimg,
            'name' => $name,
            'userimgme'=>$userimgme,
            'type_id' => $type_id,
            'pic'    => $pic,
            'loginname' => $loginname
        ]);
    }



    public function messlist(){
        $loginname = Session::get('name');

        $chatmodel = new guestChat();
        $usermodel = new User();
        $type_id = Session::get('type_id');
        // halt($type_id);
        $pic = Session::get('pic');
        $fromid = session('id');
        if (empty($fromid)) {
            return $this->error('你还未登录',"/index/login/login");
        }

        $toid =  $this->get_toid($fromid);

        $userimg = $usermodel->where('id' , $fromid) -> field('pic')->find();
        $name =$usermodel->where('id' , $toid) -> field('name')->find();
        $check = $chatmodel->where(['fromid'=>$fromid])->whereor(['toid' =>$fromid])->select();

        $userimg= $userimg['pic'];
        $name=$name['name'];
        return $this->fetch('',[
            'check' => $check,
            'fromid'=>$fromid,
            'toid'=>$toid,
            'userimg' => $userimg,
            'name' => $name,
            'type_id' => $type_id,
            'pic'    => $pic,
            'loginname' => $loginname
        ]);
    }

    /**
     * 根据fromid获取最新消息用户的id
     * @param $fromid
     * @return mixed
     */
    public function get_toid($fromid)
    {
        $chatmodel = new guestChat();
        $info =  $chatmodel->where('toid',$fromid)
            ->order('id desc')
            ->limit(1)
            ->find();
        return $info['fromid'];
    }


    /**
     * 寻人找物客服聊天记录页面
     */
    // public function customer_userlist(){
    //
    //     $chatmodel = new guestChat();
    //     $usermodel = new User();
    //     $fromid = session('id');
    //     $info = Db::table('guest_chat')->field('fromid,toid,fromname')->where('toid',$fromid)->order('time')->group('fromid')->select();
    //     //循环重组数据
    //
    //
    //
    //     $rows = array_map(function ($res){
    //         $message = $this->getLastMessage($res['fromid'],$res['toid']);
    //         $user = $usermodel
    //             ->alias('a')
    //             ->join('love_user_detail b','a.userid = b.userid',"left")
    //             ->field('name,phone,sex,status,a.userid,name')
    //             ->where('a.userid',$res['fromid'])
    //             ->find();
    //         return [
    //             'head_url'=>$this->get_head_one($res['fromid']),
    //             'name'=>$res['fromname'],
    //             'countNoread'=>$this->getCountNoread($res['fromid'],$res['toid']),
    //             'time'=>$message['time'],
    //             'remarks'=>$this->UserRemarks($res['fromid']),
    //             'status'=>$user['status'],
    //             'name'=>$user['name'],
    //             'phone'=>$user['phone'],
    //             'sex'=>$user['sex'],
    //             'id'=>$user['userid'],
    //             'name'=>$user['name']
    //                ];
    //     },$info);
    //
    //     $volume = array();
    //     // 取得列的列表进行排序
    //     foreach ($rows as $key => $row)
    //     {
    //         $volume[$key]  = $row['time'];
    //     }
    //     array_multisort($volume,  SORT_DESC, $rows);
    //     return $this->fetch('',[
    //         'data'=>$rows
    //     ]);
    // }


    /**
     * 聊天记录页
     */

    public function record(){
        $fromid = session('id');

        $toid = input('param.id');
        return $this->fetch('',[
            'fromid'=>$fromid,
            'toid'=>$toid
        ]);
    }

    public function record_list(){
        $chatmodel = new guestChat();
        $usermodel = new User();
        if(request()->isAjax()){
            $fromid = input('post.fromid');
            $toid = input('post.toid');
            $message =   $chatmodel ->where('(fromid=:fromid and toid=:toid) || (fromid=:toid1 and toid=:fromid1)',['fromid'=>$fromid,'toid'=>$toid,'toid1'=>$toid,'fromid1'=>$fromid])->order('id')->select();
            foreach ($message as $k=>$v){
                $message[$k]['time'] = date('Y-m-d H:i:s',$message[$k]['time']);
            }
            return ['message'=>$message,'toheadimg' => $this ->get_head_one($toid)];
        }
    }

    /**
     * 获取用户备注信息
     * @param $uid
     * @return mixed
     */
    // public function UserRemarks($uid){
    //     $info = Db::table('chat_remarks')
    //         ->where('userid',$uid)
    //         ->find();
    //     return $info['remarks'];
    // }




   /**
     * 查询用户头像ajax
     */
    // public function get_head(){
    //     if(request()->isAjax()){
    //         $toid = input('post.toid');
    //         $toinfo = Db::table('zhiye_user')->where('userid',$toid)->field('headimg')->find();
    //         $url_type = explode('/',$toinfo['headimg']);       //分割图片URL,判断是否七牛云图片
    //         if ($url_type[1]=='Uploads'){
    //             $toinfo['headimg'] = 'http://www.myzhaozhao.com'.$toinfo['headimg'];
    //         }elseif ($url_type[1]=='uploads'){
    //             $toinfo['headimg'] = 'http://find.myzhaozhao.com'.$toinfo['headimg'];
    //         }
    //         return $this->result($toinfo['headimg'],1,'ok');
    //     }
    // }


    public function get_head(){

        $usermodel = new User();
        if(request()->isAjax()){
            $toid = input('post.toid');

            $toinfo = $usermodel->where('id',$toid)->field('pic')->find();



            return $this->result($toinfo['pic'],1,'ok');
        }
    }



    /**
     * 查询用户昵称ajax
     */
    public function get_name(){

        $usermodel = new User();
        if(request()->isAjax()){
            $toid = input('post.toid');
            $toinfos = $usermodel ->where('id',$toid)->field('name')->find();
            $name = !empty($toinfo['name']) ? $toinfo['name'] : $toinfos['name'];
            return $this->result($name,1,'ok');
        }
    }

    /**
     * 根据用户id返回用户姓名
     */
    public function getName($uid){

        $usermodel = new User();
        $userinfo = $usermodel->where('id',$uid)->field('name')->find();

        return $userinfo['name'];

    }



    /**
     * 根据id查询用户头像
     * @param $toid
     * @return mixed
     */
    public function get_head_one($uid){
            $usermodel = new User();

            $toinfo =  $usermodel ->where('id',$uid)->field('pic')->find();



            return $toinfo['pic'];

    }

    /**
     *文本消息的数据持久化
     */
    public function save_message(){
        $chatmodel = new guestChat();
        $usermodel = new User();
        if(request()->isAjax()){
            $message = input("post.");
            $date =$usermodel->where('id',$message['fromid'])->field('name')->find();

            $datas['fromid'] = $message['fromid'];
            $datas['fromname'] = $date['name'];
            $datas['toid'] = $message['toid'];
            $datas['toname'] = $this->getName($datas['toid']);
            $datas['content'] = $message['data'];
            $datas['time'] = time();
//            $datas['isread']=$message['isread'];
            $datas['isread'] = 0;
            $datas['type'] = 1;
            $chatmodel ->insert($datas);

        }
    }

    /**
     * 图片上传，返回路径名
     */
    public function uploadimg(){
        $chatmodel = new guestChat();
        $usermodel = new User();
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
        $up = new Image();
        $name = $up->upload_img();
        $data['content'] = $name;
        $data['fromid'] = $fromid;
        $data['toid'] = $toid;
        $data['fromname'] = $this->getName($fromid);
        $data['toname'] = $this->getName($toid);
        $data['isread'] = 0;
        $data['time'] = time();
        $data['type'] = 2 ;
        $message_id = $chatmodel ->insertGetId($data);   //插入数据后获取ID
        $time = date("Y-m-d H:i:s", $data['time']);
        if ($message_id){
            return $this->result(['img_name'=>$name,'time'=>$time],1,'OK');
        }else{
            return $this->result('',0,'图片发送失败');
        }
    }
    /**
     * 页面加载返回聊天记录
     */
    public function get_load(){
        $chatmodel = new guestChat();
        $usermodel = new User();
        if(request()->isAjax()){
            $fromid = input('post.fromid');
            $toid = input('post.toid');
            $count =  Db::table('guest_chat')->where('(fromid=:fromid and toid=:toid) || (fromid=:toid1 and toid=:fromid1)',['fromid'=>$fromid,'toid'=>$toid,'toid1'=>$toid,'fromid1'=>$fromid])->count('id');

            if($count>=20){
                $message =   $chatmodel ->where('(fromid=:fromid and toid=:toid) || (fromid=:toid1 and toid=:fromid1)',['fromid'=>$fromid,'toid'=>$toid,'toid1'=>$toid,'fromid1'=>$fromid])->order('id')->select();
            }else{
                $message =   $chatmodel ->where('(fromid=:fromid and toid=:toid) || (fromid=:toid1 and toid=:fromid1)',['fromid'=>$fromid,'toid'=>$toid,'toid1'=>$toid,'fromid1'=>$fromid])->order('id')->select();
            }
            //halt($this->to_headimg($toid));
            return ['message'=>$message,'toheadimg'=>$this->get_head_one($toid)];
        }
    }

    /**
     * 获取聊天列表数据
     * @return array
     */
    public function get_list(){
        $chatmodel = new guestChat();
        $fromid = input('fromid');
        $info = $chatmodel ->field('fromid,toid,fromname,content')->where('toid',$fromid)->order('time')->group('fromid')->select();



        //循环重组数据

        //如果聊天记录为空
        $rows = array_map(function ($res){
            $message = $this->getLastMessage($res['fromid'],$res['toid']);
            $message['time'] =date('Y-m-d h:i',$message['time']);
            $content = $message['content'];
            $pos = strpos($content,'.jpg');

            if (!empty($pos)) {
                $message['content']="图片";
            }
            return [
                'head_url'=>$this->get_head_one($res['fromid']),
                'name'=>$res['fromname'],
                'countNoread'=>$this->getCountNoread($res['fromid'],$res['toid']),
                'last_message'=> $message,
                'message'=> $message['content'],
                'time'=> $message['time'],
                'chat_page'=>"http://hr.myzhaozhao.com/index/customerlist/customerlist?toid={$res['fromid']}"
            ];
        },$info);
        // 取得列的列表进行排序
        foreach ($rows as $key => $row)
        {
            $volume[$key]  = $row['time'];
        }
        array_multisort($volume,  SORT_DESC, $rows);

        return $rows;
    }

    /**
     * @param $fromid
     * @param $toid
     * 根据fromid和toid来获取他们聊天的最后一条数据
     */
    public function getLastMessage($fromid,$toid){

        $chatmodel = new guestChat();
         $info = $chatmodel
            ->where('(fromid=:fromid && toid=:toid) || (fromid=:fromid2 && toid=:toid2)',['fromid'=>$fromid,'toid'=>$toid,'fromid2'=>$toid,'toid2'=>$fromid])
            ->order('id desc')
            ->limit(1)
            ->find();
         return $info;

    }


    /**
     * @param $fromid
     * @param $toid
     * 根据formid来获取fromid同toid发送的未读消息
     */
    public function getCountNoread($fromid,$toid){
        $chatmodel = new guestChat();
        $count = $chatmodel->where(['fromid'=>$fromid,'toid'=>$toid,'isread'=>0])->count();
        return $count;
    }

    /**
     *修改未读消息成已读
     */
    // public function changeNoRead(){
    //
    //     $fromid = input('fromid');
    //     $toid = input('toid');
    //     $res = Db::table('guest_chat')->where(['fromid'=>$toid,'toid'=>$fromid])->update(['isread'=>1]);
    //     return $res;
    //
    // }

    //删除聊天列表

    public function del_message()
    {
        $chatmodel = new guestChat();
        if(!request()->isAjax()){
           return $this->result(['',0,'请求不合法']);
        }else{
            $id = input('post.id');

            $res = $chatmodel ->where('id',$id)->update(['isread'=>2]);
            if ($res){
                return $this->result('customer/customer',1,'删除成功！');
            }else{
                return $this->result('',0,'删除失败！');
            }
        }
    }

    /**
     * 清空聊天记录
     */
    // public function record_del(){
    //     $chatmodel = new guestChat();
    //     if(!request()->isAjax()){
    //         return $this->result('',0,'请求不合法');
    //     }else{
    //         $fromid = session('id');
    //         $toid = input('post.userid');
    //         try{
    //             $res =      $chatmodel->where('(fromid=:fromid and toid=:toid) || (fromid=:toid1 and toid=:fromid1)',['fromid'=>$fromid,'toid'=>$toid,'toid1'=>$toid,'fromid1'=>$fromid])->delete();
    //         }catch (\Exception $e){
    //             return $this->result('',0,$e->getMessage());
    //         }
    //         //删除备注信息
    //         if ($res){
    //             $bz =  Db::table('chat_remarks')->where('userid',$toid)->find();
    //             if (!empty($bz)){
    //                 Db::table('chat_remarks')->where('userid',$toid)->delete();
    //             }
    //         }
    //         if ($res>0){
    //             return $this->result(['jump_url' => $_SERVER['HTTP_REFERER']],1,'删除成功！');
    //         }else{
    //             return $this->result('',0,'删除失败！');
    //         }
    //     }
    // }

}
