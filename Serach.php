<?php
namespace app\index\controller;

use think\Controller;
use app\index\model\Tree;
use app\index\model\Jobget;
use app\index\model\Jobpost;
use app\index\model\Attachment;
use think\Session;
use think\Cache;
class Serach extends Base
{
    public function serach()
    {

        $getmodel = new Jobget();
        $loginname = Session::get('name');
        $type_id  = Session::get('type_id');
        $pic = Session::get('pic');
        $treemodel = new Tree();
        $cache  =   new Cache();
        $province  = $treemodel ->where( array('pid'=>1) )->select();

        // $page = $this->request->has('page') ? $this->request->param('page', 0, 'intval') : 0;//筛选第一级三级联动菜单的数据
        // $this->assign([
        //  'pageid' => $page,
        // ]);
        if ($this->request->isPost()) {
            $post =$this->request->post();

            if (!empty($post['keyword']) && !empty($post['check'])) {

                //分页标识
                $pagecid = $post['keyword'];

                $datecid = time();

                $cache->set('timecid',$datecid,8400);
                $datetimecid  = $cache->get('timecid');

                $pageallcid ='pagecid'.$datetimecid;
                $result2  =  $cache->set($pageallcid,$pagecid,8400);


                $where['addData|jobtime|ckeditor|ckeditor2|toplevel|homepage|name|jobtype|jobtime|town|title|hometype']=  ['like','%'.$post['keyword'].'%'];


                    $page =  $post['pageidup'];

                    $pageInfo =  getPageInfoslow($page,3,$where,$getmodel);
                    $datetimepage = time();

                    $cache->set('timepage',$datetimepage,3600);
                    $datetimepage  = $cache->get('timepage');

                    $pageall ='pageall'.$datetimepage;
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


                        $datetime = time();

                        $cache->set('time',$datetime,8600);

                        $datetime  = $cache->get('time');

                        $dateall ='dateall'.$datetime;

                        $result =  $cache->set($dateall,$res,8600);
                    return $this->success('正在查询...');
                }
            }elseif(empty($post['keyword']) && !empty($post['check'])){
                return $this->error('请输入搜索条件！');
            }

            $datetimecid  = $cache->get('timecid');
            $pageallcid ='pagecid'.$datetimecid;
            $cache->rm($pageallcid);


            $where  =  emptycheck($post,'addData');
            $where2 =  emptycheck($post,'town');
            $province = emptycheck($post,'province');
            $where3 =  emptycheck($post,'homemoney');
            $where4 =  emptycheck($post,'toplevel');
            $where5 =  emptycheck($post,'jobtime');
            $where6 =  emptycheck($post,'jobtype');

            $page =  $post['pageid'];

            $pageInfo =  getPageInfo($page,3,$type_id,$cache, $post,$getmodel,addData($where),jobname($where2,$province,$treemodel),homemoney($where3),toplevel($where4),jobtime($where5),jobtype($where6)); //进入模型层执行分页操作

            $datetimepage = time();
            $cache->set('timepage',$datetimepage,3600);
            $datetimepage  = $cache->get('timepage');

            $pageall ='pageall'.$datetimepage;
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

            $dateall ='dateall'.$datetime;

            $res = Cache::get($dateall);
            //分页------------------------------------------------------------

            $datetimepage  = Cache::get('timepage');

            $pageall= 'pageall'.$datetimepage;

            $pageInfo= Cache::get($pageall);
           //分页标识------------------------------------------------------------

            $datetimecid  = $cache->get('timecid');

            $pageallcid ='pagecid'.$datetimecid;
            $pagecid  =  $cache->get($pageallcid);

            if (!empty($pagecid)) {
                $this->assign([
                    'pagecid' => $pagecid
                ]);
            }



            if (empty($res)) {
             $res  =  $getmodel->field('s.*, b.filepath')
             ->table(['jobget'=>'s','attachment'=>'b'])
             ->where('s.thumb2 = b.id')->order('id desc')->where(['is_show' => 1])->limit(6)->select();
            }


            return $this->fetch('serach/serach',[

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

    public function getRegion(){
        $Region=Model("Tree");
        $map['pid']=input("get.pid");
        $map['type']=input("get.type");
        $list=$Region->where($map)->select();
        echo json_encode($list);
    }


}
