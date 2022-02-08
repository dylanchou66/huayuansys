<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Dict extends Backend
{
    
    /**
     * Dict模型对象
     * @var \app\admin\model\Dict
     */
    protected $model = null;
    protected $noNeedRight=['check'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Dict;
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
    //检查字典是否重复
    public function check()
    {
        $name = $this->request->post();
        $map['name'] = $name['row']['name'];
        $count = $this->model->where($map)->count();
        if ($count > 0) {
            $this->error(__('字典重复'));
        }
        $this->success();
    }
}
