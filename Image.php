<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/28
 * Time: 16:56
 */
namespace app\index\controller;


use app\common\lib\Upload;
use think\Controller;

class Image extends Base
{
    /**
     * 七牛图片上传ajax版
     */
    public function upload(){
        try{
            $image = Upload::image();
        }catch(\Exception $e){
            return $this->result('','0',$e->getMessage());
        }
        if($image){
            $data = [
                'status' => 1,
                'message'=> '图片上传成功！',
                'data'   =>  config('qiniu.image_url').'/'.$image
            ];
            echo json_encode($data);
        }else{
            return $this->result('','0','图片上传失败');
        }
    }

    /**
     * 七牛图片上传后端调用版
     */
    public function upload_img(){
        try{
            $image = Upload::image();
        }catch(\Exception $e){
            return $this->result('','0',$e->getMessage());
        }
        if($image){
            $img_name = config('qiniu.image_url').'/'.$image;
            return $img_name;
        }else{
            return $this->result('','0','图片上传失败');
        }
    }
}
