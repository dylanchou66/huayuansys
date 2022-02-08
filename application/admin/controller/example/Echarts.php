<?php

namespace app\admin\controller\example;

use app\admin\model\Car;
use app\admin\model\Supplier;
use app\admin\model\SupplierInfo;
use app\common\controller\Backend;

/**
 * 统计图表示例
 *
 * @icon   fa fa-charts
 * @remark 展示在FastAdmin中使用Echarts展示丰富多彩的统计图表
 */
class Echarts extends Backend
{
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('AdminLog');
    }

    /**
     * 查看
     */
    public function index()
    {
        //定义提醒月份
        $mouth = time() + 7776000;
        //查询供应商总数
        $s = new Supplier();
        $supplier_list = $s->select();
        $s_count = count($supplier_list);
        $count['scount'] = $s_count;
        //查供应商报价
        $si = new SupplierInfo();
        $supplier_info_list = $si->select();
        $s_l_count = count($supplier_info_list);
        $count['slcount'] = $s_l_count;
        //查车总数
        $c = new Car();
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

        $this->assign('count', $count);
        return $this->view->fetch();
    }

    /**
     * 详情
     */
    public function detail($ids)
    {
        $row = $this->model->get(['id' => $ids]);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $this->view->assign("row", $row->toArray());
        return $this->view->fetch();
    }
}
