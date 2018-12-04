<?php
namespace app\index\controller;

use think\Controller;
use think\Db;
use app\index\model\Tree;
use app\index\model\Jobpost;
use app\index\model\Attachment;
use app\index\model\User;
use think\Session;

class Postjod extends Base
{
    public function postjod()
    {
        $loginname = Session::get('name');

        $type_id = Session::get('type_id');
        $pic = Session::get('pic');
        $userid =session('id');
        if (empty($userid)) {
            return $this->error('你还未登录',"/index/login/login");
        }
        // halt($type_id);

        $model = new Tree();
        $imgmodel = new Attachment();
        $fromid = 1;
        $toid = 1;
        $datetime = time();
        $datetime= intval(date('Y',$datetime));//获取年份，为前端下拉年份做准备

        $province = $model ->where ( array('pid'=>1) )->select ();//筛选第一级三级联动菜单的数据
        return $this->fetch('postjod/postjod',[
            'fromid' => $fromid,
            'toid' => $toid,
            'province' => $province,
             'datetime' => $datetime,
             'type_id' => $type_id,
             'pic'    => $pic,
             'loginname' => $loginname
        ]);
    }

    public function getRegion(){
        $Region=Model("Tree");
        $map['pid']=input("get.pid");
        $map['type']=input("get.type");
        $list=$Region->where($map)->select();
            echo json_encode($list);
    }


    /**
     * 图片上传，返回路径名
     */
    public function uploadimg(){
        $file = $_FILES['file'];
        $fromid = input('post.fromid');
        $toid = input('post.toid');
        $suffix = strtolower(strrchr($file['name'],'.'));    //获取文件后缀
        $type = ['.jpg','.jpeg','.gif','.png'];
        if(!in_array($suffix,$type)){
            return ['status'=>0,'msg'=>'图片格式不正确'];
        }
        //判断图片格式是否大于5120kb
        if ($file['size']/1024>51200){
            return ['status'=>0,'msg'=>'图片过大'];
        }
        $up = new Image();
        $name = $up->upload_img();

        $data['filepath'] = $name;

        $message_id = Db::table('attachment')->insertGetId($data);   //插入数据后获取ID
        $time = date("Y-m-d H:i:s",time());
        if ($message_id){
            return $this->result(['img_name'=>$name,'time'=>$time,'message_id'=> $message_id ] ,1,'OK');
        }else{
            return $this->result('',0,'图片发送失败');
        }
    }


    public function postdata(){
        $model = new Jobpost();
        $treemodel = new Tree();
        $usermodel = new User();
        if($this->request->isPost())  {
            $post = $this->request->post();



            $validate =new \think\Validate([
                ['title', 'require|max:300','标题不能为空'],
                ['addData', 'require',  '请选择工作地点 '],
                ['jobtype', 'require',  '请选择工作类型 '],
                ['homemoney', 'require',  '请选择工资 '],
                ['name', 'require',  '请输入姓名 '],
                ['comeday','require',  '请输入出生年份'],
                ['email','require|/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.[a-zA-Z0-9]{2,6}$/',  '请填写你的邮箱|邮箱格式不正确'],
                ['tel','require|/^1[345678]{1}\d{9}$/',  '请填写你的手机号|手机号格式有误'],
                ['toplevel','require',  '请填写你的学历'],
                ['thumb2','require',  '请上传你的照片'],
                // ['twon', 'require|/^[1-9]\d*(\.\d+)?$/',  '请选择你要从事的工作'],
            ]);
            if (!$validate->check($post)) {
                    $this->error('提交失败：' . $validate->getError());
            }
            if (!empty($post['town'])) {
                $addData =$treemodel->find($post['town']) ;
                $post['town'] = $addData['name'];
            }

            if (!empty($post['newjob'])) {
                $post['town'] = $post['newjob'];
            }

            $post['user_id'] = session('id');

            $usertype = $usermodel->where(['id'=>$post['user_id']])->where(['type_id'=>1])->find();
            if (empty($usertype)) {
                return $this->error('你不是求职者');
            }
            $res = $model->putdata($post);
            if (false == $res ) {
                return $this->error('生成简历失败');
            }else {
                unset($post['tel']);
                $userres = $usermodel -> putdata($post);
                if (false== $userres) {
                    return $this->error('修改个人信息失败');
                }

             $res = $model->deledata(session('id'));
             return $this->success('生成简历成功!','/index/resume/resume');
            }
        }

    }

}
