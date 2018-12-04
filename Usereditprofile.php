<?php
namespace app\index\controller;

use think\Controller;
use think\Validate;
use think\Db;
use app\index\model\Userput;
use think\Session;
class Usereditprofile extends Base
{
    public function usereditprofile()
    {   $type_id = Session::get('type_id');
    // halt($type_id);
    $this->assign([
        'type_id' => $type_id
    ]);
        $fromid = 1;
        $toid = 1;

        return $this->fetch('',[

            'fromid' => $fromid,
            'toid' => $toid,
        ]);
    }


    public function userput(){

        $model = new Userput();
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $validate = new Validate([
                ['name', 'require',  '请输入姓名 '],
                ['comeday','require',  '请输入出生年份'],
                ['address','require',  '请输入所在地'],
                ['school','require',  '请填写你的教育经历'],
                ['email','require|/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.[a-zA-Z0-9]{2,6}$/',  '请填写你的邮箱|邮箱格式不正确'],
                // ['tel','require|/^1[345678]{1}\d{9}$/',  '请填写你的手机号|手机号格式有误'],
                ['toplevel','require',  '请填写你的学历'],
                ['thumb2','require',  '请上传你的照片'],
            ]);
            if (!$validate->check($post)) {
                    $this->error('提交失败：' . $validate->getError());
            }

            $res = $model->putdata($post);
            if (false == $res) {
                return $this->error('保存资料失败');
            }else {
             return $this->success('保存资料成功!','/index/Userdashboard/Userdashboard');
        }

        }

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

}
