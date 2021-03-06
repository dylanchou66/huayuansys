<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\library\Email;

/**
 * 
 *
 * @icon fa fa-check
 */
class Check extends Backend
{
    protected $noNeedLogin = ['carCheck','partyCheck'];
    protected $noNeedRight=['carCheck','partyCheck'];
    /**
     * Check模型对象
     * @var \app\admin\model\Check
     */
    protected $model = "";

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Check;

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
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                    ->with(['dictvalues','admin','order','boss','money','pay','king'])
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);

            foreach ($list as $row) {
                
                $row->getRelation('dictvalues')->visible(['id','name']);
				$row->getRelation('admin')->visible(['id','nickname']);
                $row->getRelation('order')->visible(['id','nickname']);
                $row->getRelation('boss')->visible(['id','nickname']);
                $row->getRelation('money')->visible(['id','nickname']);
                $row->getRelation('pay')->visible(['id','nickname']);
                $row->getRelation('king')->visible(['id','nickname']);

            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    //检查车辆保险到期并发邮件
    public function carCheck()
    {
        $car = new \app\admin\model\Car();
        //条件 30天内到期 自有车 行政车 合同车除外
        $mouth = time() + 2592000;
        $whereor['checktime'] = array('elt',$mouth);
        $whereor['cylinder_time'] = array('elt',$mouth);
        $whereor['insurance_time'] = array('elt',$mouth);
        $car_check = $car->whereor(function ($query) use ($whereor){
                        $query->whereor($whereor);})->where('car_type_id','neq',56)->field('car,checktime,cylinder_time,insurance_time')->select()->toArray();//检验日期
        //查询后结果整理下

        if(count($car_check) > 0 ){
            //怕断有没有过期的
//            $text = "以下车辆，请关注。</br>";
            $carinfo = '';
            foreach ($car_check as $v){
            $carinfo .= '<b>'.$v['car'].'</b>'.'---检验时间:'.date("Y-m-d",$v['checktime']).'  保险时间:'.date("Y-m-d",$v['insurance_time']).'  钢瓶时间:'.date("Y-m-d",$v['cylinder_time']).'<br/>';
            }
            //整理好发邮件
            $email = new Email();
            $res=$email
            ->subject('车辆到期提醒')
            ->from('system@huayuanl.com','Information')
            ->to('zhujun@huayuanl.com,dw@huayuanl.com,zhoumingchao@huayuanl.com')
            ->message($carinfo,$ishtml = true)
            ->send();
            if ($res) {
                echo 'ok';
            } else {
                echo$email->getError();
            }

        }
    }

    //检查合同是否到期
    public function partyCheck(){
        $party = new \app\admin\model\Party();
        $mouth = time() + 2592000;
        $mouth = date('Y-m-d',$mouth);
        $where['last_time_end'] = array('elt',$mouth);
        $where['status'] = array('neq',2);
        $result = $party->where($where)->field('id,number,party_a,party_b,last_time_str,last_time_end')->select()->toArray();
        if(count($result) > 0){
            $partyinfo = '';
            foreach ($result as $v){
                $partyinfo .= '合同编号:'.$v['id'].'，名称:'.$v['number'].'，到期时间:'.$v['last_time_end'].'。<br/>';
            }
            //整理好发邮件
            $email = new Email();
            $res=$email
                ->subject('合同到期提醒')
                ->from('system@huayuanl.com','Information')
                ->to('likang@huayuanl.com,dw@huayuanl.com,zhoumingchao@huayuanl.com')
                ->message($partyinfo,$ishtml = true)
                ->send();
            if ($res) {
                echo 'ok';
            } else {
                echo$email->getError();
            }
        }
    }

}
