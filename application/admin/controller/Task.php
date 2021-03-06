<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\library\Email;
use think\Db;
use think\exception\PDOException;
use function Qiniu\explodeUpToken;


/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Task extends Backend
{

    /**
     * Task模型对象
     * @var \app\admin\model\Task
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Task;

    }

    public function import()
    {
        parent::import();
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看待审核
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);

        if ($this->request->isAjax()) {

            //先判断当前用户 所负责的审核
            $c = new \app\admin\model\Check();
            $quanxian = $c->select();
            $quanxian = $quanxian->toArray();


            $qres = $this->deep_in_array($this->auth->id,$quanxian);
            if($this->auth->id != 1){
                $qres = array_search($this->auth->id,$qres);
            }

            switch($qres){
                case 'check_leder_id':
                    $field = 'leder';
                    break;
                case 'check_order_id':
                    $field ='order';
                    $w['leder_status'] = 1;
                    break;
                case 'check_boss_id':
                    $field ='boss';
                    $w['leder_status'] = 1;
                    $w['order_status'] = 1;
                    break;
                case 'check_king_id':
                    $field ='king';
                    $w['boss_status'] = 1;
                    $w['leder_status'] = 1;
                    $w['order_status'] = 1;
                    break;
                case 'check_money_id':
                    $field ='money';
                    $w['leder_status'] = 1;
                    $w['order_status'] = 1;
                    $w['boss_status'] = 1;
                    $w['king_status'] = 1;
                    break;
                case 'check_pay_id':
                    $field ='pay';
                    $w['king_status'] = 1;
                    $w['leder_status'] = 1;
                    $w['order_status'] = 1;
                    $w['boss_status'] = 1;
                    $w['money_status'] = 1;
                    break;
            }

            //个别用户除外
            if($this->auth->id == 1){
                $w = '';
                $whereor = '';
            }else{
                $w[$field.'_id'] = $this->auth->id; //当前用户
                $w[$field.'_status'] = null;  //已审核的条件
                $w['check_status'] = null; //完成

                if($qres == 'check_pay_id'){
                    $whereor['pay_status'] = 4;
                }else{
                    $whereor = '';
                }
            }
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();


            $list = $this->model
                    ->with(['admin','carpay','dictv','dict','claims','paypay','borrow','deduction','bill','collection'])
                //这是闭包

                    ->where($where)
                    ->where($w)
                ->whereor(function ($query) use ($whereor){
                    $query->whereor($whereor);
                })
                    ->order($sort, $order)
                    ->paginate($limit);
//            dump($this->model->getLastSql());exit;


            foreach ($list as $key => $row) {
                    //检查查询判断那个任务
                switch($row['task_type_id']){
                    case 66:
                        $row->getRelation('carpay')->visible(['id','pay_type_id','car_id','money','newtime']);
                        $row['ctime'] = date('Y-m-d',$row['carpay']['newtime']);
                        break;
                    case 125:
                    case 129:
                    case 130:
                        $row->getRelation('claims')->visible(['id','pay_type_id','money','date']);
                        $row['clatime'] = explode(" ",$row['claims']['date']);
                        $row['clatime'] = $row['clatime'][0];
                        break;
                    case 158:
                        $row->getRelation('paypay')->visible(['id','pay_type_id','pay_money','newtime']);
                        $row['ptime'] = date('Y-m-d',$row['paypay']['newtime']);
                        break;
                    case 215:
                    case 221:
                        $row->getRelation('borrow')->visible(['id','pay_type_id','money','newtime']);
                        $row['btime'] = date('Y-m-d',$row['borrow']['addtime']);
                        break;
                    case 235:
                        $row->getRelation('deduction')->visible(['id','money','addtime']);
                        $row['dtime'] = date('Y-m-d',$row['deduction']['addtime']);
                        break;
                    case 247:
                        $row->getRelation('bill')->visible(['id','money','addtime']);
                        $row['rtime'] = date('Y-m-d',$row['bill']['addtime']);
                        break;
                    case 251:
                        $row->getRelation('collection')->visible(['id','money','addtime']);
                        $row['ctime'] = date('Y-m-d',$row['collection']['addtime']);
                        break;
                }
                    $row->getRelation('admin')->visible(['id','nickname']);
                    $row->getRelation('dictv')->visible(['id','name']);
                    $row->getRelation('dict')->visible(['id','name']);

            }


            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }

        return $this->view->fetch();

    }

    //审批操作
    public function check(){

        //先判断是否操作过了
//        $id = isset($id) ? $_GET['id'] : '';
        //操作时间增加
        $id = input('id');
        if($id){
            $nid = $this->model->where('orders_id',$id)->field('id')->find()->toArray();
            $check = input('type');
            $T = new \app\admin\model\Task();
            //先查出审核数据
            $trres = $T->where('id',$nid['id'])->field(array('leder_id','order_id','boss_id','money_id','pay_id'))->find();

            //检查是否正常流程
            $checkres = array_search($this->auth->id,$trres->toArray());
            if(!$checkres){
                $this->error('网络错误，请联系管理员。');
            }else{
                //处理字段名
                $status = explode('_',$checkres);
                $new = $status[0].'_status';
                $w['id'] = $nid['id'];
//            $up[$new] = $check;
                $up[$status[0].'_time'] = time();
                if($new == 'pay_status'){
//                $up['check_status'] = 1;
                }
                $res = $T->where($w)->update($up);
                if($res == 1){
                    $this->success('审批成功');
                }else{
                    $this->error('网络错误，请联系管理员。');
                }
            }
        }else{

        }
    }

    public function jindu($ids){
        //查出taskid
        $car = new \app\admin\model\CarPay();
        $res = $car->where('id',$ids)->field('task_id')->find();
        $res = $res->toArray();
        $row = $this->model->get(['id' => $res['task_id']]);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if ($this->request->isAjax()) {
            $this->success("Ajax请求成功", null, ['id' => $ids]);
        }
//        dump($row);exit;
        $this->view->assign("row", $row->toArray());
        $this->view->assign("id", $ids);
        return $this->view->fetch();
    }
    //批量审批
    public function multi($ids = "")
    {
        if (!$this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ? $ids : $this->request->post("ids");
        $ids = explode(',',$ids);

        $sum = count($ids,0);

        if ($ids and $sum > 1) {
            if ($this->request->has('params')) {
                parse_str($this->request->post("params"), $values);
//                $values = $this->auth->isSuperAdmin() ? $values : array_intersect_key($values, array_flip(is_array($this->multiFields) ? $this->multiFields : explode(',', $this->multiFields)));
//                if ($values) {

                    $adminIds = $this->getDataLimitAdminIds();
                    if (is_array($adminIds)) {
                        $this->model->where($this->dataLimitField, 'in', $adminIds);
                    }
                    $count = 0;


                    $T = new \app\admin\model\Task();
                    $cp = new \app\admin\model\CarPay();
                    $ad = new \app\admin\model\Admin();
                    $mail = new Email();
                    $check = input();
                    $check = $check['params'];
                    foreach ($ids as $k => $task_id) {
                        $trres = $T->where('id',$task_id)->field(array('adduser','leder_id','order_id','king_id','boss_id','money_id','pay_id'))->find();
                        $checkres = array_search($this->auth->id,$trres->toArray());
                        //处理字段名
                        $status = explode('_',$checkres);
                        $new = $status[0].'_status';
                        $newid = $status[0].'_id';
                        $w['id'] = $task_id;
                        $up[$new] = $check;
                        $up[$status[0].'_time'] = time();

                        //出纳操作

                        if($new == 'pay_status'){
                            $this->error("不支持批量操作");
                        }else {

                            //不是出纳审核，成功不改变最后状态。失败改变2张表审批状态
                            if ($check == 2) {
                                $tup['check_status'] = 2;
                                $tup[$new] = 2;
                                $tup[$status[0].'_time'] = time();
                                $cpup['status'] = 2;
                                $res = $T->where('id', $task_id)->update($tup);
                                $res = $cp->where('task_id', $task_id)->update($cpup);


                            } else {
//                                //查下个用户邮箱
                                switch ($new) {
                                    case 'order_status':
                                        $emailuser = 'boss_id';
                                        break;
                                    case 'boss_status';
                                        $emailuser = 'money_id';
                                        break;
                                    case 'money_status';
                                        $emailuser = 'pay_id';
                                        break;
                                }

                                //如果状态为money审核 只改变carpay状态
                                if ($new == 'money_status') {
                                    $ww['task_id'] = $task_id;
                                    $upa['status'] = 1;
                                    $cp->where($ww)->update($upa);
                                }

                                $res = $T->where($w)->update($up);


//                                $email = $ad->where('id', $tuserid[$emailuser])->field('id,email')->find()->toArray();
                                //准备文字内容
                                $url = 'http://sys.dscloud.me:888/uBPmpTIrcF.php/task?ref=addtabs';
                                $txt = '您有一笔待审核业务单，请点击连接查看。<br/><a href="' . $url . '">点击访问</a>';



                            }
                        }
                    }


                    if($res == 1){
                        $this->success('批量审批成功');
                    }else{
                        $this->error(__('网络错误，请联系管理员'));
                    }
                } else {
                    $this->error(__('You have no permission'));
                }
        }else{
            $this->error("只限多条记录审核");
        }
//        }

    }


    //查看所有审核单
//    public function all()
//    {
//        //当前是否为关联查询
//        $this->relationSearch = true;
//        //设置过滤方法
//        $this->request->filter(['strip_tags', 'trim']);
//        if ($this->request->isAjax()) {
//
//            //如果发送的来源是Selectpage，则转发到Selectpage
//            if ($this->request->request('keyField')) {
//                return $this->selectpage();
//            }
//            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
//
//            $list = $this->model
//                ->with(['admin','carpay','dictv','dict'])
//
//                ->where($where)
//                ->order($sort, $order)
//                ->paginate($limit);
//            foreach ($list as $key => $row) {
//                $row->getRelation('admin')->visible(['id','nickname']);
//                $row->getRelation('carpay')->visible(['id','pay_type_id','car_id']);
//                $row->getRelation('dictv')->visible(['id','name']);
//                $row->getRelation('dict')->visible(['id','name']);
//            }
//
//            $result = array("total" => $list->total(), "rows" => $list->items());
//
//            return json($result);
//        }
//        return $this->view->fetch();
//    }

//多维数组查找
//    public function findArray($arr,$val){
//
//        $a[1] ='';
//
//        foreach($arr as $k => $v){
//            $res = array_search($val,$v);
//            dump($v);
//
//
////            if($res != false and $res != 'id'){
//                $a[$k] = $res;
////            }else{
//////                unset($arr[$k]);
////            }
//        }
//
//        dump($res);exit;
////        return $a[1];
//    }

    function deep_in_array($value, $array) {
        foreach($array as $item) {
            if(!is_array($item)) {
                if ($item == $value) {
                    return $item;
                } else {
                    continue;
                }
            }
            if(in_array($value, $item)) {
                return $item;
            } else if($this->deep_in_array($value, $item)) {
                return $item;
            }
        }
        return false;
    }

}
