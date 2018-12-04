<?php
namespace app\index\controller;

use think\Controller;
use app\index\model\User;
use app\index\model\Attachment;
use app\index\model\Jobpost;
use app\index\model\Jobget;
use app\common\model\Save;
use think\Session;
use think\Db;

class Index extends Base
{
    public function index()
    {
        $type_id = Session::get('type_id');
        $pic = Session::get('pic');
        $loginname = Session::get('name');
        $savemodel = new Save();

          $jobnum  =  $savemodel->countdate('User',2);
          $mannum  =  $savemodel->countdate('User',1);
          $jobput  =  $savemodel->countdate('Jobpost','');
          $manput  =  $savemodel->countdate('Jobget','');
          $adddata   = $savemodel->groupadd($type_id,'addData');
          $town      = $savemodel->groupadd($type_id,'town');
          $jobtime   = $savemodel->groupadd($type_id,'addData');
          $jobtype   = $savemodel->groupadd($type_id,'jobtype');
          $homemoney = $savemodel->groupadd($type_id,'homemoney');

        return $this->fetch('',[
            'type_id' => $type_id,
            'adddata'  => $adddata ,
            'town'  => $town      ,
            'jobtime'  => $jobtime   ,
            'homemoney'  => $homemoney ,
            'jobtype'  => $jobtype   ,
            'jobnum' => $jobnum,
            'mannum' => $mannum,
            'jobput' => $jobput,
            'manput' => $manput,
            'pic'    => $pic,
            'loginname' => $loginname
        ]);
    }


    //退出登录
    // public function log_off()
    // {
    //     // halt(222);
    //     if (!request()->isAjax()) {
    //         return $this->result('',0,'非法参数错误');
    //     }else {
    //         if (Session::has('id')) {
    //             Session::delete('id');
    //             Session::delete('type_id');
    //             return $this->result('',1,'登出成功');
    //         }else{
    //             return $this->result('',1,'登出成功');
    //         }
    //         return $this->result('',0,'退出失败');
    //     }
    // }
}
