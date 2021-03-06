<?php


namespace app\admin\controller;


use app\admin\model\DeductionInfo;
use app\admin\model\DictValues;
use app\admin\model\Task;
use app\common\controller\Backend;
use app\common\library\Email;
use fast\Arr;
use think\Db;
use think\exception\PDOException;

/**
 * 表格联动
 * 点击左侧日志列表，右侧的表格数据会显示指定管理员的日志列表
 * @icon fa fa-table
 */
class Deduction extends Backend
{
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Claims');
    }


    public function table1()
    {
//        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            $cysCount = $this->model->where("zeren = '0' AND deduction = '0'")->field('responsibility_id')->group('responsibility_id')->count();
            $fgsCount = $this->model->where("zeren = '1' AND deduction = '0'")->field('fgs_id')->group('fgs_id')->count();
            $shCount = $this->model->where("zeren = '2' AND deduction = '0'")->field('responsibility_id')->group('responsibility_id')->count();
            $wdzCount = $this->model->where("zeren = '3' AND deduction = '0'")->field('project_id')->group('project_id')->count();
            $khCount = $this->model->where("zeren = '4' AND deduction = '0'")->field('project_id')->group('project_id')->count();
            $cysSum = $this->model->where("zeren = '0' AND deduction = '0'")->sum('money');
            $fgsSum = $this->model->where("zeren = '1' AND deduction = '0'")->sum('money');
            $shSum = $this->model->where("zeren = '2' AND deduction = '0'")->sum('money');
            $wdzSum = $this->model->where("zeren = '3' AND deduction = '0'")->sum('money');
            $khSum = $this->model->where("zeren = '4' AND deduction = '0'")->sum('money');
            $list = array('0'=>array('id'=>'0','name'=>'承运商','number'=>$cysCount,'money'=>round($cysSum,2)
            ),'1'=>array('id'=>'1','name'=>'其他分公司','number'=>$fgsCount,'money'=>round($fgsSum,2)
            ),'2'=>array('id'=>'2','name'=>'上海分公司','number'=>$shCount,'money'=>round($shSum,2)
            ),'3'=>array('id'=>'3','name'=>'未定责','number'=>$wdzCount,'money'=>round($wdzSum,2)
            ),'4'=>array('id'=>'4','name'=>'客户','number'=>$khCount,'money'=>round($khSum,2)
            )
                );





            $result = array( "rows" => $list,"total" => count($list),);

            return json($result);
        }
        return $this->view->fetch('index');
    }

    public function table2()
    {
                $this->relationSearch = true;

        $in = input();
        $type = $in['filter'];
        $type = json_decode($type,true);
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            $type['id'] = isset($type['id']) ? $type['id']:'';
            if($type['id'] != ''){
                $map['zeren'] = $type['id'];//类型
                $map['deduction'] = 0;

            }else{
                $map['zeren'] = 5;
                $map['deduction'] = 0;
            }
            switch($map['zeren']){
                case 0:
                    $S = new \app\admin\model\Supplier();
                    $list = model('Claims')->where($map)->field('responsibility_id,cast(sum(`money`) AS DECIMAL (19, 2))as money,count(`responsibility_id`) as ttl')->group('responsibility_id')->select();
                    foreach ($list as $row) {
                        //查询名称
                        $res = $S->where('id',$row['responsibility_id'])->field('company')->find()->toArray();
                        $row['cys_name'] = $res['company'];
                        $row['type'] = $map['zeren'];
                        $row['type_id'] = $row['responsibility_id'];
                    }
                    $result = array("total" => count($list),"rows" => $list);
                    break;
                case 1:
                    $D = new \app\admin\model\DictValues();
                    $list = model('Claims')->where($map)->field('fgs_id,cast(sum(`money`) AS DECIMAL (19, 2)) as money,count(`fgs_id`) as ttl')->group('fgs_id')->select();
                    foreach ($list as $row) {
                        //查询名称
                        $res = $D->where('id',$row['fgs_id'])->field('name')->find()->toArray();
                        $row['cys_name'] = $res['name'];
                        $row['type'] = $map['zeren'];
                        $row['type_id'] = $row['fgs_id'];
                    }
                    $result = array("total" => count($list),"rows" => $list);
                    break;

                case 2:
                case 3:
                    $P = new \app\admin\model\Project();
                    $list = model('Claims')->where($map)->field('project_id,cast(sum(`money`) AS DECIMAL (19, 2)) as money,count(`project_id`) as ttl')->group('project_id')->select();
                    foreach ($list as $row) {
                        //查询名称
                        $res = $P->where('id',$row['project_id'])->field('project')->find()->toArray();
                        $row['cys_name'] = $res['project'];
                        $row['type'] = $map['zeren'];
                        $row['type_id'] = $row['project_id'];
                    }
                    $result = array("total" => count($list),"rows" => $list);
                    break;
                case 4:
                    $P = new \app\admin\model\Project();
                    $list = model('Claims')->where($map)->field('project_id,cast(sum(`money`) AS DECIMAL (19, 2)) as money,count(`project_id`) as ttl')->group('project_id')->select();
                    foreach ($list as $row) {
                        //查询名称
                        $res = $P->where('id',$row['project_id'])->field('company')->find()->toArray();
                        $row['cys_name'] = $res['company'];
                        $row['type'] = $map['zeren'];
                        $row['type_id'] = $row['project_id'];
                    }
                    $result = array("total" => count($list),"rows" => $list);
                    break;
                case 5:
                    $w['id'] = 0;
                    $P = new \app\admin\model\Project();
                    $list = model('Claims')->where($w)->field('project_id,cast(sum(`money`) AS DECIMAL (19, 2)) as money,count(`project_id`) as ttl')->group('project_id')->select();
                    foreach ($list as $row) {
                        //查询名称
                        $res = $P->where('id',$row['project_id'])->field('company')->find()->toArray();
                        $row['cys_name'] = $res['company'];
                        $row['type'] = $map['zeren'];
                        $row['type_id'] = $row['project_id'];
                    }
                    $result = array("total" => count($list),"rows" => $list);
                    break;
            }
            return json($result);
        }
        return $this->view->fetch('index');
    }


    public function table3()
    {
        $this->relationSearch = true;

        $in = input();
        $type = $in['filter'];
        $type = json_decode($type,true);
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            $type['type'] = isset($type['type']) ? $type['type']:'';
            if($type['type'] != ''){
                $map['zeren'] = $type['type'];//类型
                $map['deduction'] = 0;
            }else{
                $map['zeren'] = 5;
                $map['deduction'] = 0;
            }

            switch($map['zeren']){
                case 0:
                    $map['responsibility_id'] = $type['type_id'];
                    $list = model('Claims')->where($map)->field('claims_id,money,id,date,zeren')->select();
                    $result = array("total" => count($list),"rows" => $list);
                    break;
                case 1:
                    $map['fgs_id'] = $type['type_id'];
                    $list = model('Claims')->where($map)->field('claims_id,money,id,date,zeren')->select();
                    $result = array("total" => count($list),"rows" => $list);
                    break;
                case 2:
                case 3:
                case 4:
                    $map['project_id'] = $type['type_id'];
                    $list = model('Claims')->where($map)->field('claims_id,money,id,date,zeren')->select();
                    $result = array("total" => count($list),"rows" => $list);
                    break;
                case 5:
                    $map['id'] = 0;
                    $list = model('Claims')->where($map)->field('claims_id,money,id,date,zeren')->select();
                    $result = array("total" => count($list),"rows" => $list);
                    break;
            }
            return json($result);
        }
        return $this->view->fetch('index');
    }

    public function deduction($ids = "")
    {
        if (!$this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ? $ids : $this->request->post("ids");
        if ($ids) {
            if ($this->request->has('params')) {


                    $adminIds = $this->getDataLimitAdminIds();
                    if (is_array($adminIds)) {
                        $this->model->where($this->dataLimitField, 'in', $adminIds);
                    }
                    $count = 0;
                    Db::startTrans();
                    try {
                        //先存入记录表
                        $D = new DeductionInfo();
                        $Claims = new \app\admin\model\Claims();
                        $t = new \app\admin\model\Task();
                        $C = new \app\admin\model\Check();

                        //改变索赔表状态
                        $list = explode(',',$ids);
                        $ttl = 0;
                        foreach ($list as $key => $v) {
                            $result = $Claims->where('id',$v)->update(array('deduction'=>1));
                            //计算总金额
                            $tres = $Claims->where('id',$v)->field('money,zeren,responsibility_id,fgs_id')->find()->toArray();
                            $ttl += $tres['money'];
                        }

                        $da['ids'] = $ids;
                        $da['adduser'] = $this->auth->id;
                        $da['addtime'] = time();
                        $da['status'] = 0;
                        $da['money'] = $ttl;
                        $da['zeren'] = $tres['zeren'];
                        $da['zeren_id'] = $tres['responsibility_id'];
                        $da['fgs_id'] = $tres['fgs_id'];
                        $d_id = $D->insertGetId($da);
                        $addid = 'HY-SH-D-'.date("Ymd",time()).'-'.$d_id;
                        $up['deduction_one_id'] = $addid;
                        $w['id'] = $d_id;
                        $D->where($w)->update($up);
                        //存入task表
                        $check = $C->where('check_id',221)->find()->toArray();
                        $data['orders_id'] = $d_id;
                        $data['adduser'] = $this->auth->id;
                        $data['addtime'] = time();
                        $data['leder_id'] = $check['check_leder_id'];
                        $data['order_id'] = $check['check_order_id'];
                        $data['boss_id'] = $check['check_boss_id'];
                        $data['money_id'] = $check['check_money_id'];
                        $data['king_id'] = $check['check_king_id'];
                        $data['leder_status'] = 1;
                        $data['order_status'] = 1;
                        $data['boss_status'] = 1;
                        $data['money_status'] = 1;
                        $data['king_status'] = 1;
                        $data['pay_id'] = $check['check_pay_id'];
                        $data['check_status'] = null;
                        $data['task_type_id'] = 235;
                        $data['pay_type_id'] = 235;
                        $data['one_id'] = 'HY-SH-D-'.date("Ymd",time()).'-'.$d_id;
                        $r = $t->insertGetId($data);
                        $w['id'] = $d_id;
                        $D->where($w)->update(['task_id'=>$r]);
                        Db::commit();
                    } catch (PDOException $e) {
                        Db::rollback();
                        $this->error($e->getMessage());
                    } catch (Exception $e) {
                        Db::rollback();
                        $this->error($e->getMessage());
                    }
                    if ($result !== false) {
                        $this->success();
                    } else {
                        $this->error(__('网络错误，联系管理员。'));
                    }
                }
            }

        $this->error(__('Parameter %s can not be empty', 'ids'));
    }





}
