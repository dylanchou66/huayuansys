<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 
 *
 * @icon fa fa-car
 */
class Car extends Backend
{
    
    /**
     * Car模型对象
     * @var \app\admin\model\Car
     */
    protected $model = null;
    protected $noNeedRight = ['check','number'];
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Car;
        $this->view->assign("otherCarList", $this->model->getOtherCarList());
        $this->view->assign("statusList", $this->model->getStatusList());
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
            $map['car_type_id'] = array('NOT IN','56,199');
//            $map['car_type_id'] = array('neq',56);

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                    ->with(['dictvalues','adduser','carid'])
                    ->where($where)
                    ->where($map)
                    ->order($sort, $order)
                    ->paginate($limit);

            foreach ($list as $row) {
                
                $row->getRelation('dictvalues')->visible(['id','name','status']);
                $row->getRelation('adduser')->visible(['id','nickname']);
                $row->getRelation('carid')->visible(['id','name']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }
    //检查固定号是否重复
    public function check()
    {
        $hyid = $this->request->post();
        $map['hyid'] = $hyid['row']['hyid'];
        $count = $this->model->where($map)->count();
        if ($count > 0) {
            $this->error(__('资产号重复'));
        }
        $this->success();
    }
    //检查车牌是否重复
    public function number()
    {
        $hyid = $this->request->post();
        $map['car'] = $hyid['row']['car'];
        $count = $this->model->where($map)->count();
        if ($count > 0) {
            $this->error(__('车牌重复'));
        }
        $this->success();
    }
}