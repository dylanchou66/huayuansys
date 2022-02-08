<?php

namespace app\admin\controller;

use app\admin\model\Car;
use app\common\controller\Backend;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Info extends Backend
{
    
    /**
     * Info模型对象
     * @var \app\admin\model\Info
     */
    protected $model = "index";

//    public function _initialize()
//    {
//        parent::_initialize();
//        $this->model = new \app\admin\model\Info;
//        $this->view->assign("statusList", $this->model->getStatusList());
//    }

//    public function import()
//    {
//        parent::import();
//    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function index()
    {
        //定义提醒月份
        $mouth = time() + 7776000;
        //查询供应商总数
        $s = new \app\admin\model\Supplier();
        $supplier_list = $s->select();
        $s_count = count($supplier_list);
        $count['scount'] = $s_count;
        //查供应商报价
        $si = new \app\admin\model\SupplierInfo();
        $supplier_info_list = $si->select();
        $s_l_count = count($supplier_info_list);
        $count['slcount'] = $s_l_count;
        //查车总数
        $c = new \app\admin\model\Car();
        $c_list = $c->select();
        $ccount = count($c_list);
        $count['car'] = $ccount;
        //查询车辆保险即将到期
        $c_baoxian_list = $c->where("insurance_time <= {$mouth}")->select();
        $cb = count($c_baoxian_list);
        $count['carbx'] = $cb;
        //查检验到期
        $c_check_list = $c->where("checktime <= {$mouth}")->select();
        $ccheck = count($c_check_list);
        $count['carcheck'] = $ccheck;
        //查钢瓶日期
        $c_cyl_list = $c->where("cylinder_time <= {$mouth}")->select();
        $ccyl = count($c_cyl_list);
        $count['carcyl'] = $ccyl;
        //查到期合同
        $party = new \app\admin\model\Party();

        $pmouth = date('Y-m-d',$mouth);
        $pwhere['status'] = array('neq',2);
        $pwhere['last_time_end'] = array('elt',$pmouth);
        $pres = $party->where($pwhere)->select();
        $count['partycheck'] = count($pres);
        $this->assign('count', $count);
        return $this->view->fetch();
    }

}
