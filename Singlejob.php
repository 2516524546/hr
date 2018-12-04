<?php
namespace app\index\controller;

use think\Controller;
use app\index\model\Jobget;
use app\index\model\Attachment;
use app\index\model\User;
use app\index\model\Myjob;
use app\common\model\Save;
use think\Session;

class Singlejob extends Base
{
    public function singlejob()
    {
     $loginname = Session::get('name');

      $type_id = Session::get('type_id');
        // halt($type_id);
        $pic = Session::get('pic');

        $dataid = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;//获取评论人的ID
        $userid =session('id');
        if (empty($userid)) {
            return $this->error('你还未登录',"/index/login/login");
        }
        $savemodel = new Save();
        $myjobmodel = new Myjob();
        $usermodel = new User();
        $model = new Jobget();
        $imgmodel = new Attachment();
        if($type_id==2){
            $check = $model->where(['user_id'=>$userid])->where(['id'=>$dataid])->find();
            $usertype = $usermodel->where(['id'=>$userid])->where(['type_id'=>2])->find();
            if (empty($check)) {
                return $this->error('非法请求!');
            }

             if(empty($usertype)){
                 return $this->error('无数据!');
             }
             $myjob ="";

        }else {
              $myjob = $myjobmodel->where(['myid'=>$userid])->where(['jobid'=>$dataid])->where(['level'=>1])->find();
              $namedata   = $savemodel->groupadd($type_id,'name');

        }


        $job = $model->where(['id'=>$dataid])->order('id desc')->limit(1)->select();
        if (empty($job)) {
            return $this->error('没有发布工作','/index/getjob/getjob');
        }

        $jobimg = $imgmodel->where('id','>',0)->select();


        $jobimg1 =$this->imguser($jobimg,$job);
        $jobimg2 =$this->imguser2($jobimg,$job);

        return $this->fetch('',[
            'namedata' => $namedata ,
            'job' => $job,
            'jobimg1' => $jobimg1,
            'jobimg2' => $jobimg2,
             'myjob' =>$myjob,
             'type_id' => $type_id,
             'pic'    => $pic,
             'loginname' => $loginname
        ]);

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

   public function  imguser2($userimg,$user){

       foreach ($userimg as $key => $value) {
            foreach ($user as $key2 => $value2) {
                if ($value2['thumb3']==$value['id']) {
                    return $value['filepath'];
                }
            }
       }
  }




}
