<?php
namespace app\index\controller;

use think\Controller;
use app\index\model\Tree;
use app\index\model\User;
use app\index\model\Jobget;
use app\index\model\Attachment;
use think\Session;
class Getjob extends Base
{
    public function getjob()
    {   $loginname = Session::get('name');

        $pic = Session::get('pic');
        $type_id = Session::get('type_id');
        // halt($type_id);
        $userid =session('id');

        if (empty($userid)) {
            return $this->error('你还未登录',"/index/login/login");
        }

        $model = new Tree();
        $province = $model ->where ( array('pid'=>1) )->select ();
        $fromid = 1;
        $toid = 1;
        $datetime = time();
        $datetime= intval(date('Y',$datetime));

        return $this->fetch('',[
            'province' => $province,
            'fromid' => $fromid,
            'toid' => $toid,
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
        $imgdata =new  Attachment();
        $name = $up->upload_img();

        $data['filepath'] = $name;

        $message_id = $imgdata->insertGetId($data);   //插入数据后获取ID
        $time = date("Y-m-d H:i:s",time());
        if ($message_id){
            return $this->result(['img_name'=>$name,'time'=>$time,'message_id'=> $message_id ] ,1,'OK');
        }else{
            return $this->result('',0,'图片发送失败');
        }
    }



        public function putdata(){
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
                    ['thumb2','require',  '请上传贵司的LOGO'],
                    ['thumb3','require',  '请上传贵司的营业执照'],
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
                $usertype = $usermodel->where(['id'=>$post['user_id']])->where(['type_id'=>2])->find();
                if (empty($usertype)) {
                    return $this->error('你不是企业');
                }
                $res = $model->putdata($post);
                if (false == $res ) {
                    return $this->error('发布工作失败');
                }else {
                    unset($post['tel']);
                    $userres = $usermodel-> putdata($post);
                     return $this->success('发布工作成功!','/index/companydashjobs/companydashjobs');
                }






            }

        }



}
