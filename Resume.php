<?php
namespace app\index\controller;

use think\Controller;
use app\index\model\Jobpost;
use app\index\model\Attachment;
use app\index\model\User;
use app\index\model\Jobget;
use app\index\model\Myjob;
use think\Session;

class Resume extends Base
{
    public function resume()
    {
        $loginname = Session::get('name');
        $pic = Session::get('pic');
        $userid =session('id');
        if (empty($userid)) {
            return $this->error('你还未登录',"/index/login/login");
        }
        $type_id = Session::get('type_id');
        $usermodel = new User();
        $model = new Jobpost();
        $getmodel = new Jobget();
        $imgmodel = new Attachment();
        $myjobmodel = new Myjob();

        $firstid = $this->request->has('firstid')? $this->request->param('firstid',0,'intval') : 0;

        if ($firstid!=0) {

            $jobid = $this->request->has('pid')? $this->request->param('pid',0,'intval') : 0;

            $userid = $firstid;


            $userinfo = $usermodel->where(['id'=>$jobid])->find();
            $userimg = Attachment::where('id','>',0)->select();

            $alldata =$getmodel->field('s.*') //截取表s的name列 和表a的全部
                ->table(['jobget'=>'s','user'=>'c'])
                ->where('c.id = s.user_id')
                ->where(['type_id' => 2])
                ->where(['s.user_id' =>session('id')])
                ->select();
            $resjob = $this->imguserroot($userimg,$alldata);



            $alldata2 =$getmodel->field('b.*') //截取表s的name列 和表a的全部
                ->table(['myjob'=>'a','jobpost'=>'s','jobget'=>'b'])
                ->where(['b.user_id' => session('id')])
                ->where('a.jobid = b.id')
                ->where('s.user_id = a.myid')
                ->where(['a.level' => 1])
                ->select();

            $resjob2 = $this->imguserroot($userimg,$alldata2);




            $user = $model->where(['id'=>$userid])->order('id desc')->limit(1)->select();

        }else {

            $resjob ="";
            $resjob2= "";
            $usertype = $usermodel->where(['id'=>$userid])->where(['type_id'=>1])->find();
            if (empty($usertype)) {
                return $this->error('非法请求!');
            }
            $user = $model->where(['user_id'=>$userid])->order('id desc')->limit(1)->select();
        }




        if (empty($user)) {

             return $this->error('未创建简历!','/index/postjod/postjod');

        }
        $skills = $this->skills($user);

        $userimg = $imgmodel->where('id','>',0)->select();

        $userimg =$this->imguser($userimg,$user);

        return $this->fetch('resume/resume',[
            'user' => $user,
            'userimg' => $userimg,
            'skills' => $skills,
            'type_id' => $type_id,
            'resjob2' => $resjob2,
            'resjob'=> $resjob,
            'firstid' =>$firstid,
            'pic'    => $pic,
            'loginname' => $loginname
        ]);

    }

    public function  imguserroot($userimg,$job){
        foreach ($userimg as $key => $value) {
             foreach ($job as   &$value2) {
                 if ($value2['thumb2']==$value['id']) {
                     $value2['filepath'] = $value['filepath'];
                 }
             }

        }
        return $job;
    }

     public function  imguser($userimg,$user){

         foreach ($userimg as $key => $value) {
              foreach ($user as $key2 => $value2) {
                  if ($value2['thumb2']==$value['id']) {
                      return $value['filepath'];
                  }
              }
         }
    }


    public function skills($user){
        foreach ($user as $key2 => $value2) {
            if (strpos($value2['tags'], ",") != false) {
                $skills = explode(',',$value2['tags']);
                return $skills;
            }
            else {
                 $skills[0]='';
                 $skills[1]=$value2['tags'];
                 return $skills;
            }
        }
    }


}
