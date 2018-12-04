<?php
namespace app\index\controller;

use think\Controller;
use app\index\model\User;
use app\index\model\Attachment;
use app\index\model\Jobpost;
use app\index\model\Jobget;
use think\Session;
use think\Cache;
class Companydashresume extends Base
{
    public function companydashresume()
    {
        $loginname = Session::get('name');

        $pic = Session::get('pic');
        $usermodel = new User();
        $imgmodel = new Attachment();
        $getmodel = new Jobget();
        $type_id = Session::get('type_id');
        // halt($type_id);
        $userid = session('id');

        if (empty($userid)) {
            return $this->error('你还未登录',"/index/login/login");
        }
        $userinfo = $usermodel->where(['id'=>$userid])->find();
        $userimg = Attachment::where('id','>',0)->select();

        // $userid = $getmodel->where(['user_id'=>$userid])->field('id')->find();
        //
        // $userid = $userid['id'];

        $alldata =$getmodel->field('s.*') //截取表s的name列 和表a的全部
            ->table(['myjob'=>'a','jobpost'=>'s','jobget'=>'b'])
            ->where(['b.user_id' => $userid])
            ->where('a.jobid = b.id')
            ->where('s.user_id = a.myid')
            ->where(['a.level' => 1 ])
            ->select();

        $alldata = $this-> jobdouble($alldata);    

        $res = $this->imguser($userimg,$alldata);

        return $this->fetch('',[
            'res' => $res,
            'type_id' => $type_id,
            'pic' => $pic,
            'loginname' => $loginname
        ]);


    }


    //查看应聘者简历
    public function jobresume()
    {

        $pic = Session::get('pic');
        $type_id = Session::get('type_id');
        // halt($type_id);
        $this->assign([
            'type_id' => $type_id
        ]);

        $type_id = Session::get('type_id');
        // halt($type_id);
        $this->assign([
            'type_id' => $type_id
        ]);
        return $this->fetch();
    }

    //企业发布的邀请
    public function companyinvite()
    {
        $loginname = Session::get('name');

        $pic = Session::get('pic');
        $imgmodel = new Attachment();
        $postmodel = new Jobpost();
        $getmodel = new Jobget();

        $type_id = Session::get('type_id');

        $userid = session('id');
        $userimg = Attachment::where('id','>',0)->select();


        $alldata =$getmodel->field('b.*') //截取表s的name列 和表a的全部
            ->table(['myjob'=>'a','jobget'=>'s','jobpost'=>'b'])
            ->where(['s.user_id' => $userid])
            ->where('s.id = a.myid')
            ->where('b.id = a.jobid')
            ->where(['a.level'=> 2])
            ->where(['b.is_show'=>1])
            ->select();

            $alldata = $this-> jobdouble($alldata);

            $res = $this->imguser($userimg,$alldata);

            return $this->fetch('',[
                'res' => $res ,
                'type_id' => $type_id,
                'pic'    => $pic,
                'loginname' => $loginname
            ]);

    }


    public function  jobdouble($job){
        foreach ($job as $key => $value) {
            foreach ($job as $key2 => $value2) {
                if (!empty($job[$key])) {
                    if ($key != $key2 ) {
                            // echo $key.'---'.$key2.'//' ;
                    if ($job[$key2]['id']==$job[$key]['id']) {
                        unset($job[$key2]);
                     }
                 }else {
                     continue;
                 }
            }

            }

        }

        return $job;
    }

    public function cutdown($cutdate){
        $cutdateall=[];
        $cutdateall[]=$cutdate;


        return $cutdateall;

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


}
