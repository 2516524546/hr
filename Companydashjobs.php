<?php
namespace app\index\controller;

use think\Controller;
use app\index\model\Tree;
use think\Db;
use think\Session;
use app\index\model\Jobget;
use app\index\model\Attachment;
use app\index\model\User;
use app\index\model\Myjob;

class Companydashjobs extends Base
{
    public function companydashjobs()
    {
        $loginname = Session::get('name');

        $pic = Session::get('pic');
        $userid =session('id');
        if (empty($userid)) {
            return $this->error('你还未登录',"/index/login/login");
        }

        $type_id = Session::get('type_id');

        $userid =session('id');
        $getData = input('get.');
        $page =  isset($getData['page'])  ? $getData['page'] : 1;
        $field = isset($getData['field']) ? $getData['field'] : '';
        $order = isset($getData['order']) ? $getData['order'] : '';
        $sortInfo = ['field' => $field, 'order' => $order];
        $where['user_id'] = ['=',$userid];
        $pageInfo = Jobget::getPageInfo($page,2,$where); //进入模型层执行分页操作

        $job =Jobget::getTopics($pageInfo['page'],2,$sortInfo,$where);//

        $userimg = Attachment::where('id','>',0)->select();

        $res = $this->imguser($userimg,$job);

        return $this->fetch('',[
         'res' => $res ,
         'type_id' => $type_id,
         'pic'    => $pic,
         'loginname' => $loginname,
         'page' =>       $pageInfo['page'],
         'pageNum' =>    $pageInfo['pageNum'],
         'showPages' =>  $pageInfo['showPages'],
        ]);
    }


         public function  imguser($userimg,$job){
              foreach ($userimg as $key => $value) {
                   foreach ($job as   &$value2) {
                       if ($value2['thumb2']==$value['id']) {
                           $value2['img'] = $value['filepath'];
                       }
                   }
              }
              return $job;
          }

    //编辑页面
    public function editthejob()
    {
        $loginname = Session::get('name');

        $pic = Session::get('pic');
        $userid =session('id');
        if (empty($userid)) {
            return $this->error('你还未登录',"/index/login/login");
        }
        $type_id = Session::get('type_id');
        // halt($type_id);
        $this->assign([
            'type_id' => $type_id
        ]);
        $model = new Jobget();
        $modeltree = new Tree();
        $userimg = Attachment::where('id','>',0)->select();
        $province = $modeltree ->where ( array('pid'=>1) )->select ();
        $fromid = 1;
        $toid = 1;
        $datetime = time();
        $datetime= intval(date('Y',$datetime));
        $dataid =  $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
        $datathum= $dataid+1;
        $job = $model->where(['id'=>$dataid])-> find();

        $res = $this->imguserfind($userimg,$job,$datathum);


        return $this->fetch('companydashjobs/editthejob',[
            'res' => $res,
            'province' => $province,
            'fromid' => $fromid,
            'toid' => $toid,
            'datetime' => $datetime,
            'pic'    => $pic,
            'loginname' => $loginname,
        ]);
    }


    public function  imguserfind($userimg,&$job,$datathum){
        foreach ($userimg as $key => $value) {

                 if ($job['thumb2']==$value['id'] ) {
                      $job['img'] = $value['filepath'];
                 }
                 if ($job['thumb3']== $value['id']) {
                      $job['img2'] = $value['filepath'];
                 }

             }


        return $job;
    }



    public function makejob(){


        $userid =session('id');
        if (empty($userid)) {
            return $this->error('你还未登录',"/index/login/login");
        }
        $makejobmodel =   new Myjob();
        $check = $this->request->has('pid')?$this->request->param('pid',0,'intval'): 0 ;

        $post['level'] = Session::get('type_id');
        if ($post['level']==1) {
            $post['jobid'] = $this->request->has('id')?$this->request->param('id',0,'intval'): 0 ;
            $post['myid']= session('id');
                    if ($check==1) {
                        $res = $makejobmodel->deletedata($post,$post['jobid'],session('id'),$post['level']);
                        if ($res) {
                            return $this->success('已取消应聘');
                        }
                        else {
                            return $this->error('取消应聘失败！');
                        }
                    }
                    $res = $makejobmodel->putdata($post);
                    if (false == $res ) {
                        return $this->error('应聘失败！');
                    }
                    else {
                        return $this->success('已应聘！');
                    }
    }else {

        $post['myid'] = $this->request->has('intoid')?$this->request->param('intoid',0,'intval'): 0 ;

        $post['jobid'] = $this->request->has('jobid')?$this->request->param('jobid',0,'intval'): 0 ;

                if ($check==1) {
                    $res = $makejobmodel->deletedata($post,$post['jobid'],$post['myid'] ,$post['level']);
                    if ($res) {
                        return $this->success('已取消邀请');
                    }
                    else {
                        return $this->error('取消邀请失败！');
                    }
                }
                $res = $makejobmodel->putdata($post);
                if (false == $res ) {
                    return $this->error('邀请失败！');
                }
                else {
                    return $this->success('已邀请！');
                }


    }



    }


//删除页面
    public function delejob(){
        $userid = session('id');
        if (empty($userid)) {
            return $this->error('你还未登录',"/index/login/login");
        }
        $id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
        $check =  Jobget::where(['id'=>$id])->where(['user_id'=>$userid])->find();
        if (empty($check)) {
            return $this->error('非法请求！');
        }else {
             $res = Jobget::where(['id'=>$id])->where(['user_id'=>$userid])->delete();
             if ($res) {
                 Myjob::where(['myid'=>$id])->where(['level'=>2])->delete();
                 return $this->success('删除成功!');
             }else {
                 return $this->error('删除失败！');
             }
         }
    }

    public function putdata(){
        $userid =session('id');
        if (empty($userid)) {
            return $this->error('你还未登录',"/index/login/login");
        }
        $model = new Jobget();
        $treemodel = new Tree();
        $usermodel = new User();
        if($this->request->isPost())  {
            $post = $this->request->post();


            $validate =new \think\Validate([
                ['title', 'require|max:300','招聘标题不能为空'],
                ['name', 'require|max:300','公司名称不能为空'],
                ['addData', 'require',  '请输入公司地点 '],
                ['hometype', 'require',  '请选择公司性质 '],
                ['homebig', 'require',  '请输入工资 '],
                ['email','require|/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.[a-zA-Z0-9]{2,6}$/',  '请填写你的邮箱|邮箱格式不正确'],
                // ['tel','require|/^1[345678]{1}\d{9}$/',  '请填写你的手机号|手机号格式有误'],


                // ['twon', 'require|/^[1-9]\d*(\.\d+)?$/',  '请选择你要从事的工作'],
            ]);
            if (!$validate->check($post)) {
                    $this->error('提交失败：' . $validate->getError());
            }
            if (!empty($post['town'])) {
                $addData =$treemodel->find($post['town']) ;
                $post['town'] = $addData['name'];
            }

            $post['user_id'] = session('id');

            $check = $model->where(['user_id'=>$post['user_id']])->where(['id'=>$post['id']])->find();
            if (empty($check)) {
                return $this->error('非法请求!');
            }
            $usertype = $usermodel->where(['id'=>$post['user_id']])->where(['type_id'=>2])->find();
            if (empty($usertype)) {
                return $this->error('你不是企业');
            }
            $res = $model->updatedata($post,$post['id']);
            $userres = $usermodel-> putdata($post);
            if (false == $res) {
                return $this->error('修改工作失败,您未做任何改动');
            }else {
             return $this->success('修改工作成功!','/index/singlejob/singlejob/id/'.$post['id']);
            }
        }
    }




}
