<?php
namespace app\index\controller;

use think\Controller;
use think\Db;
use app\index\model\User as UserModel;
use app\index\model\Tree;
use app\index\model\Attachment;
use think\Session;
use think\Cache;
use app\index\model\Jobget;
use app\index\model\Jobpost;
class User extends Base
{
    public function user()
    {
        $loginname = Session::get('name');

        $pic = Session::get('pic');
        $getmodel = new Jobpost();
        $type_id  = Session::get('type_id');
        $treemodel = new Tree();
        $cache  =   new Cache();
        $imgmodel = new Attachment();

        $province  = $treemodel ->where( array('pid'=>1) )->select();//筛选第一级三级联动菜单的数据
        if ($this->request->isPost()) {
            $post =$this->request->post();
            // 设置缓存
            if (!empty($post['keyword']) && !empty($post['check'])) {

                //分页标识
                $pagecid = $post['keyword'];

                $datecid = time();

                $cache->set('timecid',$datecid,8400);
                $datetimecid  = $cache->get('timecid');

                $pageallcid ='pagecid2'.$datetimecid;
                $result2  =  $cache->set($pageallcid,$pagecid,8400);

                $where['addData|jobtime|ckeditor|ckeditor2|ckeditor3|name|jobtype|jobtime|town|title|toplevel|schoolname']=['like','%'.$post['keyword'].'%'];



                    $page =  $post['pageidup'];

                    $pageInfo =  getPageInfoslow($page,3,$where,$getmodel);

                    $datetimepage = time();

                    $cache->set('timepage',$datetimepage,3600);
                    $datetimepage  = $cache->get('timepage');

                    $pageall ='pageall2'.$datetimepage;
                    $result2 =  $cache->set($pageall,$pageInfo,3600);

                    $res  = $getmodel
                        ->where($where)
                        ->where(['is_show' => 1])
                        ->order('id desc')
                        ->page($pageInfo['page'],3)
                        ->select();


                    $userimg = Attachment::where('id','>',0)->select();

                    $res = $this->imguser($userimg,$res);

                    //两个数组结合

                    if (empty($res)) {
                        return $this->error('查不到数据！');
                    }else {


                    $datetime2 = time();

                    $cache->set('time',$datetime2,8600);

                    $datetime2  = $cache->get('time');

                    $dateall2 ='dateall2'.$datetime2;

                    $result =  $cache->set($dateall2,$res,8600);
                    return $this->success('正在查询...');
                }

            }elseif(empty($post['keyword']) && !empty($post['check'])){
                return $this->error('请输入搜索条件！');
            }

            $datetimecid  = $cache->get('timecid');
            $pageallcid ='pagecid2'.$datetimecid;
            $cache->rm($pageallcid);

            $where  =  emptycheck($post,'addData');
            $where2 =  emptycheck($post,'town');
            $province = emptycheck($post,'province');
            $where3 =  emptycheck($post,'homemoney');
            $where4 =  emptycheck($post,'toplevel');
            $where5 =  emptycheck($post,'jobtime');
            $where6 =  emptycheck($post,'jobtype');

            $page =  $post['pageid'];

            $pageInfo =  getPageInfo($page,3,$type_id,$cache,$post,$getmodel,addData($where),jobname($where2,$province,$treemodel),homemoney($where3),toplevel($where4),jobtime($where5),jobtype($where6)); //进入模型层执行分页操作

            $datetimepage = time();
            $cache->set('timepage',$datetimepage,3600);
            $datetimepage  = $cache->get('timepage');

            $pageall ='pageall2'.$datetimepage;
            $result2 =  $cache->set($pageall,$pageInfo,3600);

            // halt($getmodel->where(jobname($where2,$province,$treemodel))->select());
           // halt(addData($where));
            $result = union($pageInfo['page'],3,$type_id,$cache, $post,$getmodel,addData($where),jobname($where2,$province,$treemodel),homemoney($where3),toplevel($where4),jobtime($where5),jobtype($where6));
            if (false == $result) {
                return $this->error('查询不到数据！');
            }
            else {
                return $this->success('正在查询......');
            }
        }


            $datetime  = $cache->get('time');

            $dateall ='dateall2'.$datetime;

            $res = Cache::get($dateall);
            //分页------------------------------------------------------------

            $datetimepage  = Cache::get('timepage');

            $pageall = 'pageall2'.$datetimepage;

            $pageInfo = Cache::get($pageall);


            //分页标识------------------------------------------------------------

             $datetimecid  = $cache->get('timecid');

             $pageallcid ='pagecid2'.$datetimecid;
             $pagecid  =  $cache->get($pageallcid);

             if (!empty($pagecid)) {
                 $this->assign([
                     'pagecid' => $pagecid
                 ]);
             }



            if (empty($res)) {

             $res  =  $getmodel->field('s.*, b.filepath')
             ->table(['jobpost'=>'s','attachment'=>'b'])
             ->where('s.thumb2 = b.id')->order('id desc')->where(['is_show'=>1])->limit(6)->select();
            }



            return $this->fetch('user/user',[
                'res' => $res,
                'province' => $province ,
                'type_id' => $type_id ,
                'pic'    => $pic,
                'loginname' => $loginname,
                'page' =>      $pageInfo['page'],
                'pageNum' =>   $pageInfo['pageNum'],
                'showPages' => $pageInfo['showPages'],
            ]);


    }



    public function modpassword(){
        $uid = session('id');
        $res = Db::table('user')->find($uid);
        $this->assign('res',$res);
        return $this->fetch();
    }

    public function changePassword()
    {
        if(!request()->isAjax()){
            return $this->result('',0,'非法参数错误');
        }else{
            $data = input('post.');
            if($data['code'] != session('SMS.code') || $data['tel'] != session('SMS.phone')){
                return $this->result('',0,'验证码错误');
            }else{
                //$user = model('User')->get(['tel'=>$data['tel']]);
                $res = Db::table('user')->find($data['id']);
                if(!empty($res['tel'])){
                    if(md5($data['oldpass']) != $res['password']){
                        return $this->result('',0,'输入旧密码与原来的不相符');
                    }else{
                        $upd = Db::table('user')->where('id',$data['id'])->update(['password' => md5($data['newpass'])]);
                        if($upd){
                            session('id',null);
                            session('type_id',null);
                            return $this->result(url('login/login'),1,'修改成功，请重新登录');
                        }else{
                            return $this->result('',0,'密码修改失败');
                        }
                    }
                }else{
                    return $this->result('',0,'不能利用别的手机号验证');
                }
            }
        }
    }

    //我的应聘
    public function myjobs()
    {
        $loginname = Session::get('name');

        $pic = Session::get('pic');
        $usermodel = new UserModel();
        $imgmodel = new Attachment();
        $getmodel = new Jobget();
        $type_id = Session::get('type_id');
        // halt($type_id);


        $userid = session('id');

        $userinfo = $usermodel->where(['id'=>$userid])->find();
        $userimg = Attachment::where('id','>',0)->select();

        $alldata =$getmodel->field('s.*,a.*') //截取表s的name列 和表a的全部
            ->table(['myjob'=>'a','jobget'=>'s','user'=>'c'])
            ->where(['a.myid' => $userid])
            ->where('s.id = a.jobid')
            ->where('c.id = s.user_id')
            ->where(['c.type_id' => 3 ])
            ->where(['s.is_show'=>1])
            ->select();


        $res = $this->imguser($userimg,$alldata);

        return $this->fetch('',[
            'res' => $res,
            'userinfo' =>$userinfo,
            'type_id' => $type_id,
            'pic'    => $pic,
            'loginname' => $loginname
        ]);
    }


    public function  imguser($userimg,$job){
        foreach ($userimg as $key => $value) {
             foreach ($job as   &$value2) {
                 if ($value2['thumb2']==$value['id']) {
                     $value2['filepath'] = $value['filepath'];
                 }
             }

        }
        return $job;
    }


    //企业聘请
    public function bsinvite()
    {
        $loginname = Session::get('name');
        $pic = Session::get('pic');
        $usermodel = new UserModel();
        $imgmodel = new Attachment();
        $postmodel = new Jobpost();
        $getmodel = new Jobget();

        $type_id = Session::get('type_id');
        $userid = session('id');

        $userinfo = $usermodel->where(['id'=>$userid])->find();
        $userimg = Attachment::where('id','>',0)->select();

        $userid = $postmodel->where(['user_id'=>$userid])->field('id')->find();

        $userid = $userid['id'];


        $alldata =$getmodel->field('d.*') //截取表s的name列 和表a的全部
            ->table(['myjob'=>'a','jobget'=>'d'])
            ->where(['a.jobid' => $userid])
            ->where('d.id  = a.myid')
            ->where(['a.level'=> 3])
            ->where(['d.is_show'=>1])
            ->select();


        $res = $this->imguser($userimg,$alldata);

        return $this->fetch('',[
        'res' => $res ,
        'type_id' => $type_id,
        'pic'    => $pic,
        'loginname' => $loginname
        ]);
    }


}
