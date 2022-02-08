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
class Collection extends Backend
{
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Pay');
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

            $cysCount = $this->model->where("df_type = '0' AND collection = '0' AND status = '5'")->field('project_id')->group('project_id')->count();
            $fgsCount = $this->model->where("df_type = '1' AND collection = '0' AND status = '5'")->field('df_id')->group('df_id')->count();
            $shCount = $this->model->where("df_type = '2' AND collection = '0' AND status = '5'")->field('df_id')->group('df_id')->count();
            $cysSum = $this->model->where("df_type = '0' AND collection = '0' AND status = '5'")->sum('pay_money');
            $fgsSum = $this->model->where("df_type = '1' AND collection = '0' AND status = '5'")->sum('pay_money');
            $shSum = $this->model->where("df_type = '2' AND collection = '0' AND status = '5'")->sum('pay_money');
            $list = array(
                '0'=>array('id'=>'2','name'=>'客户','number'=>$shCount,'money'=>round($shSum,2)),
                '1'=>array('id'=>'1','name'=>'分公司','number'=>$fgsCount,'money'=>round($fgsSum,2)),
                '2'=>array('id'=>'0','name'=>'成本','number'=>$cysCount,'money'=>round($cysSum,2))
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
            $map['df_type'] = $type['id'];//类型
            $map['collection'] = 0;
            $map['status'] = 5;
            switch($type['id']){
                case 0:
                case 2:
                    $P = new \app\admin\model\Project();
                    $list = model('Pay')->where($map)->field('project_id,cast(sum(`pay_money`) AS DECIMAL (19, 2)) as money,count(`project_id`) as ttl')->group('project_id')->select();
                    foreach ($list as $row) {
                        //查询名称
                        $res = $P->where('id',$row['project_id'])->field('project')->find()->toArray();
                        $row['cys_name'] = $res['project'];
                        $row['type'] = $map['df_type'];
                        $row['type_id'] = $row['project_id'];
                    }
                    $result = array("total" => count($list),"rows" => $list);
                    break;
                case 1:
                    $D = new \app\admin\model\DictValues();
                    $list = model('Pay')->where($map)->field('df_id,cast(sum(`pay_money`) AS DECIMAL (19, 2)) as money,count(`df_id`) as ttl')->group('df_id')->select();
                    foreach ($list as $row) {
                        //查询名称
                        $res = $D->where('id',$row['df_id'])->field('name')->find()->toArray();
                        $row['cys_name'] = $res['name'];
                        $row['type'] = $map['df_type'];
                        $row['type_id'] = $row['df_id'];
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
            $type['type_id'] = isset($type['type_id']) ? $type['type_id']:'';


            $map['df_type'] = $type['type'];//类型
            $map['collection'] = 0;
            $map['status'] = 5;
            switch($type['type']){
                case 1:
                    $map['df_id'] = $type['type_id'];
                    $list = model('Pay')->where($map)->field('pay_one_id,pay_money,id,newtime,df_type')->select();
                    $result = array("total" => count($list),"rows" => $list);
                    break;
                case 0:
                case 2:
                    $map['project_id'] = $type['type_id'];
                    $list = model('Pay')->where($map)->field('pay_one_id,pay_money,id,newtime,df_type')->select();
                    $result = array("total" => count($list),"rows" => $list);
                    break;
            }
            return json($result);
        }
        return $this->view->fetch('index');
    }

    public function collection($ids = "")
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
                        $Coll = new \app\admin\model\Collection();
                        $Pay = new \app\admin\model\Pay();
                        $t = new \app\admin\model\Task();
                        $C = new \app\admin\model\Check();

                        //改变索赔表状态
                        $list = explode(',',$ids);
                        $ttl = 0;
                        foreach ($list as $key => $v) {
                            $result = $Pay->where('id',$v)->update(array('collection'=>1));
                            //计算总金额
                            $tres = $Pay->where('id',$v)->field('pay_money,df_type,df_id')->find()->toArray();
                            $ttl += $tres['pay_money'];
                        }

                        $da['ids'] = $ids;
                        $da['adduser'] = $this->auth->id;
                        $da['addtime'] = time();
                        $da['status'] = 0;
                        $da['money'] = $ttl;
                        $da['type'] = $tres['df_type'];
                        $da['type_id'] = $tres['df_id'];
                        $d_id = $Coll->insertGetId($da);
                        $addid = 'HY-SH-S-'.date("Ymd",time()).'-'.$d_id;
                        $up['collection_one_id'] = $addid;
                        $w['id'] = $d_id;
                        $Coll->where($w)->update($up);
                        //存入task表
                        $check = $C->where('check_id',251)->find()->toArray();
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
                        $data['task_type_id'] = 251;
                        $data['pay_type_id'] = 251;
                        $data['one_id'] = 'HY-SH-S-'.date("Ymd",time()).'-'.$d_id;
                        $r = $t->insertGetId($data);
                        $w['id'] = $d_id;
                        $Coll->where($w)->update(['task_id'=>$r]);
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
