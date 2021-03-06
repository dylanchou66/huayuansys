<?php

namespace app\admin\controller;

use app\admin\model\DictValues;
use app\common\controller\Backend;
use app\common\library\Email;
use think\Db;
use app\admin\model\Check;
use app\admin\model\Task;
/**
 * 
 *
 * @icon fa fa-circle-o
 */
class CarPay extends Backend
{
    /**
     * CarPay模型对象
     * @var \app\admin\model\CarPay
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\CarPay;
        $this->view->assign("invoiceList", $this->model->getInvoiceList());
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
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $list = $this->model
                    ->with(['car','dictvalues','adduser','free','siji','jiaqizhan'])
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);
//            dump($this->model->getLastSql());exit;
            foreach ($list as $row) {
                
                $row->getRelation('car')->visible(['id','car']);
				$row->getRelation('dictvalues')->visible(['id','name']);
                $row->getRelation('adduser')->visible(['id','nickname']);
                $row->getRelation('free')->visible(['id','name']);
                $row->getRelation('siji')->visible(['id','name']);
                $row->getRelation('jiaqizhan')->visible(['id','name']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    //添加方法，写入审核进度表
    /**
     * 添加
     */

//    //写入任务审核表调取方法
//$T = new Task;
//$T->task($params);exit;
    public function add()
    {
        $B = new \app\admin\model\Borrow();
        $qk = $B->jz_qk($this->auth->id);
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $params['adduser'] = $this->auth->id;
            if ($params) {
                $params = $this->preExcludeFields($params);

                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validateFailException(true)->validate($validate);
                    }
                    $params['jz_beizhu'] = isset($params['jz_beizhu'])?$params['jz_beizhu']:"";
                    //如果核销 写入明细
                    $params['borrow'] = isset($params['borrow'])?$params['borrow']:0;
                    if($params['borrow'] != 1){
                        unset($params['borrow_money']);
                        unset($params['car_cash']);
                        unset($params['jz_beizhu']);
                    }else{
                        $params['ps'] = $params['ps'].' 系统备注---'.$params['jz_beizhu'];
                    }

                    $result = $this->model->allowField(true)->save($params);
                    $addid = $this->model->getLastInsID();
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    //大于3万需要张总审批
                    if($params['money'] < 30000){
                        $data['king_status'] = 1;
                    }
                    //存入任务表
                    $c = new Check();
                    $t = new Task();
                    $check = $c->where('check_id',66)->find()->toArray();
                    $data['orders_id'] = $this->model->id;
                    $data['adduser'] = $this->auth->id;
                    $data['addtime'] = time();
                    $data['leder_id'] = $check['check_leder_id'];
                    $data['order_id'] = $check['check_order_id'];
                    $data['boss_id'] = $check['check_boss_id'];
                    $data['money_id'] = $check['check_money_id'];
                    $data['king_id'] = $check['check_king_id'];
                    $data['pay_id'] = $check['check_pay_id'];
                    $data['check_status'] = null;
                    $data['task_type_id'] = 66;
                    $data['pay_type_id'] = $params['pay_type_id'];
                    $data['one_id'] = 'HY-SH-C-'.date("Ymd",time()).'-'.$addid;
                    if($check['check_leder_id'] == 24){
                        $data['leder_status'] = 1;
                        $data['leder_time'] = time();
                        //发邮件给下一个审核人 先查询用户邮箱
                        $admin  = new \app\admin\model\Admin();
                        $email = $admin->where('id',$check['check_order_id'])->field('id,email')->find()->toArray();
                        //准备文字内容
                        $url = 'http://sys.dscloud.me:888/uBPmpTIrcF.php/task?ref=addtabs';
                        $txt = '您有一笔待审核业务单，请点击连接查看。<br/><a href="'.$url.'">点击访问</a>';

                        $mail = new Email();
                        $mail
                            ->subject('《车辆付费》审核提醒，审核单：'.$data['one_id'])
                            ->from('system@huayuanl.com','Information')
                            ->to($email['email'])
                            ->message($txt,$ishtml = true)
                            ->send();

                    }
                    $r = $t->insertGetId($data);
                    $w['id'] = $this->model->id;
                    $result = $this->model->where($w)->update(['task_id'=>$r]);
                    if($result != 1){
                        $this->error('网络错误，请联系管理员');
                    }

                    //存入id账号到pay_car
                    $one_id = 'HY-SH-C-'.date("Ymd",time()).'-'.$addid;
                    $d_w['id'] = $addid;
                    $da['one_pay_id'] = $one_id;
                    $this->model->where($d_w)->update($da);

                    //存入借支表
                    if($params['borrow'] == 1){
                        $B = new \app\admin\model\Borrow();
                        $r = $B->car_add($this->auth->id,$params,$addid,$r,$data['one_id']);
                    }
                    //存入卡表
                    if($params['pay_type_id'] == 248 or $params['pay_type_id'] == 249){
                        $Bill = new \app\admin\model\Bill();
                        $Bill->bill_add($this->auth->id,$params,$addid,$r,$data['one_id']);
                    }
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("qk", $qk);

        return $this->view->fetch();
    }

    //查看单条数据
    public function detail($ids)
    {
        //先去付费表查
        $cp = new \app\admin\model\Task();
        $oid = $cp->where(['id'=>$ids])->field('orders_id,task_type_id')->find()->toArray();
        $row = $this->model->get(['id' => $oid['orders_id']]);

        if($oid['task_type_id'] == 125){
            $claims = new Claims();
            $result = $claims->detail($ids);

            exit;
        }

        if (!$row) {
            $this->error(__('No Results were found'));
        }

        //先查出审核数据
        $T = new \app\admin\model\Task();
        $trres = $T->where('id',$ids)->field(array('adduser','leder_id','king_id','order_id','boss_id','money_id','pay_id'))->find();
        //检查是否正常流程
        $checkres = array_search($this->auth->id,$trres->toArray());
        //处理字段名
        $status = explode('_',$checkres);
        $new = $status[0].'_status';

        if ($this->request->isAjax()) {
            $b = input();
            $beizhu = $b['row']['ps'];
            $T = new \app\admin\model\Task();
            $id = input('ids');
            $check = input('check');
//                $nid = $T->where('orders_id',$id)->field('id')->find();
//                dump($nid);exit;

            //先查出审核数据
            $trres = $T->where('id',$id)->field(array('adduser','king_id','leder_id','order_id','boss_id','money_id','pay_id','one_id'))->find();
            //检查是否正常流程
            $checkres = array_search($this->auth->id,$trres->toArray());

            if(!$checkres or $check == null){
                $this->error('请选择审批类型');
            }else{
                $mail = new Email();
                //处理字段名
                $status = explode('_',$checkres);
                $new = $status[0].'_status';

                $w['id'] = $id;
                $up[$new] = $check;
                $up[$status[0].'_time'] = time();
                //出纳操作
                $bi = $this->model->get(['task_id'=>$id])->toArray();

                if($new == 'pay_status'){
                    $up['check_status'] = $check;
                    $up['ps'] = $beizhu;
                    if($check == 1 or $check ==4){
                        // 处理任务表
                        $ww['task_id'] = $ids;
                        $cp = new \app\admin\model\CarPay();
                        if($check == 4){
                            $upa['status'] = $check;
                        }else{
                            $upa['status'] = 3;
                        }
                        $upa['pay_time'] = time();
                        $cp->where($ww)->update($upa);
                        //借支确认
                        if($bi['borrow'] == 1){
                            $B = new \app\admin\model\Borrow();
                            $m['gl_order_id'] = $bi['id'];
                            $u['status'] = 3;
                            $u['pay_time'] = time();
                            $B->where($m)->update($u);
                        }
                        //卡操作
                        if($check == 1){
                            if($b['pay_type_id'] == 248 or $b['pay_type_id'] == 249){

                                $bill = new \app\admin\model\Bill();
                                $m['task_id'] = $bi['task_id'];
                                $u['status'] = 3;
                                $u['pay_time'] = time();
                                $r = $bill->where($m)->update($u);
                            }
                        }
                        if($check == 4){
                            if($b['pay_type_id'] == 204 or $b['pay_type_id'] == 124){
                                $bill = new \app\admin\model\Bill();
                                $m['task_id'] = $bi['task_id'];
                                $u['status'] = 3;
                                $u['pay_time'] = time();
                                $r = $bill->where($m)->update($u);
                            }
                        }

                        //判断是否完成付款，结果告知最终用户
                        $ad = new \app\admin\model\Admin();


                        $adduser = $trres->toArray();
                        $adduser = $adduser['adduser'];
                        $url = 'http://sys.dscloud.me:888/uBPmpTIrcF.php/car_pay?ref=addtabs';
                        $txt = '业务审核单完成，请点击连接查看。<br/><a href="'.$url.'">点击访问</a>';
                        $email = $ad->where('id',$adduser)->field('id,email')->find()->toArray();
                        $mail
                            ->subject('审核完成，审核单：'.$trres['one_id'])
                            ->from('system@huayuanl.com','Information')
                            ->to($email['email'])
                            ->message($txt,$ishtml = true)
                            ->send();


                        //查下当前审核id是谁 再查出email

//                         dump($cp->getLastSql());exit;


                    }else{
                        $ad = new \app\admin\model\Admin();
                        // 处理任务表
                        $ww['task_id'] = $ids;
                        $cp = new \app\admin\model\CarPay();
                        $upa['status'] = 2;
                        $cp->where($ww)->update($upa);
                        //借支确认
                        if($bi['borrow'] == 1){
                            $B = new \app\admin\model\Borrow();
                            $m['gl_order_id'] = $bi['id'];
                            $u['status'] = 2;
                            $B->where($m)->update($u);
                        }
                        if($b['pay_type_id'] == 248 or $b['pay_type_id'] == 249 or $b['pay_type_id'] == 124 or $b['pay_type_id'] == 204){
                            $bill = new \app\admin\model\Bill();
                            $m['task_id'] = $bi['task_id'];
                            $u['status'] = 2;
                            $bill->where($m)->update($u);
                        }
                        $url = 'http://sys.dscloud.me:888/uBPmpTIrcF.php/car_pay?ref=addtabs';
                        $txt = '业务审核单驳回，请点击连接查看。<br/><a href="'.$url.'">点击访问</a>';
                        $adduser = $trres->toArray();
                        $adduser = $adduser['adduser'];
                        $email = $ad->where('id',$adduser)->field('id,email')->find()->toArray();
                        $mail
                            ->subject('审核完成，审核单：'.$trres['one_id'])
                            ->from('system@huayuanl.com','Information')
                            ->to($email['email'])
                            ->message($txt,$ishtml = true)
                            ->send();

//                         dump($cp->getLastSql());exit;
                    }
                }else{
                    $up['ps'] = $beizhu;
                    //不是出纳审核，成功不改变最后状态。失败改变2张表审批状态
                    $id = $ids;
                    if($check == 2){

                        $tup['check_status'] = 2;
                        $cpup['status'] = 2;
                        $T->where('id',$id)->update($tup);
                        $cp = new \app\admin\model\CarPay();
                        $cp->where('task_id',$id)->update($cpup);
                        if($bi['borrow'] == 1){
                            $B = new \app\admin\model\Borrow();
                            $m['gl_order_id'] = $bi['id'];
                            $u['status'] = 2;
                            $B->where($m)->update($u);
                        }
                        //卡操作
                        if($b['pay_type_id'] == 248 or $b['pay_type_id'] == 249 or $b['pay_type_id'] == 124 or $b['pay_type_id'] == 204){
                            $bill = new \app\admin\model\Bill();
                            $m['yw_id'] = $bi['id'];
                            $u['status'] = 2;
                            $bill->where($m)->update($u);
                        }
                    }else{
                        $ad = new \app\admin\model\Admin();
                        $task = new Task();
                        //查下个用户邮箱
                        switch($new){
                            case 'leder_status':
                                //判断是否审核过
                                $check_res = $task->where('id',$id)->field('order_id,order_status')->find()->toArray();
                                if($check_res['order_status'] == null){
                                    $emailuser = 'order_id';
                                }
                                break;
                            case 'order_status':
                                //判断是否审核过
                                $check_res = $task->where('id',$id)->field('boss_id,boss_status')->find()->toArray();
                                if($check_res['boss_status'] == null){
                                    $emailuser = 'boss_id';
                                }
                                break;
                            case 'boss_status':
                                $check_res = $task->where('id',$id)->field('king_id,king_status')->find()->toArray();
                                if($check_res['king_status'] == null){
                                    $emailuser = 'king_id';
                                }else{
                                    $emailuser = 'money_id';
                                }
                                break;
                            case 'king_status':
                                $check_res = $task->where('id',$id)->field('money_id,money_status')->find()->toArray();
                                if($check_res['money_status'] == null){
                                    $emailuser = 'money_id';
                                }
                                break;
                            case 'money_status':
                                $check_res = $task->where('id',$id)->field('pay_id,pay_status')->find()->toArray();

                                if($check_res['pay_status'] == null){
                                    $emailuser = 'pay_id';
                                }
                                break;
                        }
                        $task = new Task();

                        //如果状态为money审核 只改变carpay状态
                        if($new == 'money_status'){
                            $ww['task_id'] = $ids;
                            $cp = new \app\admin\model\CarPay();
                            $upa['status'] = 1;
                            $cp->where($ww)->update($upa);

                        }
                        $task_id = $task->where('id',$id)->field($emailuser)->find()->toArray();
                        $email = $ad->where('id',$task_id[$emailuser])->field('id,email')->find()->toArray();
                        //准备文字内容
                        $url = 'http://sys.dscloud.me:888/uBPmpTIrcF.php/task?ref=addtabs';
                        $txt = '您有一笔待审核业务单，请点击连接查看。<br/><a href="'.$url.'">点击访问</a>';


                        $mail
                            ->subject('《车辆付费》审核提醒，审核单：'.$trres['one_id'])
                            ->from('system@huayuanl.com','Information')
                            ->to($email['email'])
                            ->message($txt,$ishtml = true)
                            ->send();
                    }
                }

                $res = $T->where($w)->update($up);


                if($res == 1){
                    $this->success('审批成功');
                }else{
                    $this->error('网络错误，请联系管理员。');
                }
            }

            $this->success("Ajax请求成功", null, ['id' => $ids]);
        }
        $row = $row->toArray();
        $dv = new DictValues();
        $car = new \app\admin\model\Car();
        $user = new \app\admin\model\Admin();
        $r =  $dv->where('id',$row['pay_type_id'])->field('name')->find()->toArray();
        if($row['payinfo_id']){
            $pif = $dv->where('id',$row['payinfo_id'])->field('name')->find()->toArray();
        }
        $cr = $car->where('id',$row['car_id'])->field('car,hyid,car_type_id')->find()->toArray();

//        if($row['free_id'] != ''){
//            $fr = $dv->where('id',$row['free_id'])->field('name')->find()->toArray();
//        }
        $ps = $cp->where('id',$ids)->field('id,ps')->find()->toarray();
        $ur = $user->where('id',$row['adduser'])->field('nickname')->find()->toArray();
        $sj = $dv->where('id',$row['siji_id'])->field('name')->find()->toArray();
        $xz = $dv->where('id',$cr['car_type_id'])->field('name')->find()->toArray();
        $row['xz'] = $xz['name'];
        $row['pay_type_name'] = $r['name'];
        $row['car'] = $cr['car'];
        $row['hyid'] = $cr['hyid'];
        $pif['name'] = null;
        $row['payinfi'] = $pif['name'];
        $row['beizhu']  =$ps['ps'];
        $row['siji'] = $sj['name'];
//        if($row['free_id'] != ''){
//            $row['free'] = $fr['name'];
//        }
        $row['username'] = $ur['nickname'];
        $row['img'] = explode(',', $row['photoimages']);

        //查进度
        //查出taskid

        $jindu = $cp->get(['id' => $ids]);
        $our = $user->where('id',$jindu['order_id'])->field('nickname')->find()->toArray();
        $bur = $user->where('id',$jindu['boss_id'])->field('nickname')->find()->toArray();
        $mur = $user->where('id',$jindu['money_id'])->field('nickname')->find()->toArray();
        if($jindu['king_id'] != ''){
            $kur = $user->where('id',$jindu['king_id'])->field('nickname')->find()->toArray();
            $jindu['kur'] = $kur['nickname'];

        }
        $jindu['our'] = $our['nickname'];
        $jindu['bur'] = $bur['nickname'];
        $jindu['mur'] = $mur['nickname'];

//        dump($jindu->toArray());exit;

        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if ($this->request->isAjax()) {
            $this->success("Ajax请求成功", null, ['id' => $ids]);
        }
//        dump($jindu);exit;
        $this->view->assign("row", $row);
        $this->view->assign("new", $new);




        $this->view->assign("jindu", $jindu->toArray());


            return $this->view->fetch();


    }



    public function one($ids){
//        //先去付费表查
        $cp = new \app\admin\model\Task();
//        $oid = $cp->where(['orders_id'=>$ids])->field('id')->find()->toArray();

        $row = $this->model->get(['id' => $ids]);

        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $row = $row->toArray();

        $t = new Task();
        $dv = new DictValues();
        $car = new \app\admin\model\Car();
        $user = new \app\admin\model\Admin();
        $car_w['task_type_id'] = 66;//限定为索赔
        $car_w['orders_id'] = $row['id'];
        $tres = $t->where($car_w)->field('money_status,king_status,order_status,boss_status')->find()->toArray();

        $r =  $dv->where('id',$row['pay_type_id'])->field('name')->find()->toArray();
        if($row['payinfo_id']){
            $pif = $dv->where('id',$row['payinfo_id'])->field('name')->find()->toArray();
        }

        if($row['car_id']){
            $cr = $car->where('id',$row['car_id'])->field('car,hyid')->find()->toArray();
            $row['hyid'] = $cr['hyid'];

        }
//        if($row['free_id'] != ''){
//            $fr = $dv->where('id',$row['free_id'])->field('name')->find()->toArray();
//        }
        $ps = $cp->where('orders_id',$ids)->field('id,ps')->find()->toarray();

        $ur = $user->where('id',$row['adduser'])->field('nickname')->find()->toArray();
        $sj = $dv->where('id',$row['siji_id'])->field('name')->find()->toArray();
        $row['pay_type_name'] = $r['name'];
        $row['car'] = $cr['car'];
        $pif['name'] = null;
        $row['payinfi'] = $pif['name'];
        $row['beizhu']  =$ps['ps'];
        $row['siji'] = $sj['name'];
//        if($row['free_id'] != ''){
//            $row['free'] = $fr['name'];
//        }
        $row['username'] = $ur['nickname'];
        $row['img'] = explode(',', $row['photoimages']);

        //查进度
        //查出taskid
        $cj_w['task_type_id'] = 66;//限定为索赔
        $cj_w['orders_id'] = $ids;
        $jindu = $cp->where($cj_w)->find()->toArray();
        $our = $user->where('id',$jindu['order_id'])->field('nickname')->find()->toArray();
        $bur = $user->where('id',$jindu['boss_id'])->field('nickname')->find()->toArray();
        $mur = $user->where('id',$jindu['money_id'])->field('nickname')->find()->toArray();
        if($jindu['king_id'] != ''){
            $kur = $user->where('id',$jindu['king_id'])->field('nickname')->find()->toArray();
            $jindu['kur'] = $kur['nickname'];

        }
        $jindu['our'] = $our['nickname'];
        $jindu['bur'] = $bur['nickname'];
        $jindu['mur'] = $mur['nickname'];

//        dump($jindu->toArray());exit;

        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if ($this->request->isAjax()) {
            $this->success("Ajax请求成功", null, ['id' => $ids]);
        }
//        dump($jindu);exit;
        $this->view->assign("row", $row);
//        $this->view->assign("id", $ids);

        $this->view->assign("check", $tres);
        $this->view->assign("jindu", $jindu);
        return $this->view->fetch();
    }

    public function addhetong()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $params['adduser'] = $this->auth->id;
            if ($params) {

                $params = $this->preExcludeFields($params);

                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validateFailException(true)->validate($validate);
                    }

                    $result = $this->model->allowField(true)->save($params);
                    $addid = $this->model->getLastInsID();
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    //大于3万需要张总审批
                    if($params['money'] < 30000){
                        $data['king_status'] = 1;
                    }
                    //存入任务表
                    $c = new Check();
                    $t = new Task();
                    $check = $c->where('check_id',66)->find()->toArray();
                    $data['orders_id'] = $this->model->id;
                    $data['adduser'] = $this->auth->id;
                    $data['addtime'] = time();
                    $data['leder_id'] = $check['check_leder_id'];
                    $data['order_id'] = $check['check_order_id'];
                    $data['boss_id'] = $check['check_boss_id'];
                    $data['king_id'] = $check['check_king_id'];
                    $data['money_id'] = $check['check_money_id'];

                    $data['pay_id'] = $check['check_pay_id'];
                    $data['check_status'] = null;
                    $data['task_type_id'] = 66;
                    $data['pay_type_id'] = $params['pay_type_id'];
                    $data['one_id'] = 'HY-SH-C-'.date("Ymd",time()).'-'.$addid;
                    if($check['check_leder_id'] == 24){
                        $data['leder_status'] = 1;
                        $data['leder_time'] = time();
                        //发邮件给下一个审核人 先查询用户邮箱
                        $admin  = new \app\admin\model\Admin();
                        $email = $admin->where('id',$check['check_order_id'])->field('id,email')->find()->toArray();
                        //准备文字内容
                        $url = 'http://sys.dscloud.me:888/uBPmpTIrcF.php/task?ref=addtabs';
                        $txt = '您有一笔待审核业务单，请点击连接查看。<br/><a href="'.$url.'">点击访问</a>';

                        $mail = new Email();
                        $mail
                            ->subject('《车辆付费》审核提醒，审核单：'.$data['one_id'])
                            ->from('system@huayuanl.com','Information')
                            ->to($email['email'])
                            ->message($txt,$ishtml = true)
                            ->send();

                    }
                    $r = $t->insertGetId($data);
                    $w['id'] = $this->model->id;
                    $result = $this->model->where($w)->update(['task_id'=>$r]);

                    //存入卡表
                    $Bill = new \app\admin\model\Bill();
                    $Bill->bill_add_hetong($this->auth->id,$params,$addid,$r,$data['one_id']);


                    if($result != 1){
                        $this->error('网络错误，请联系管理员');
                    }

                    //存入id账号到pay_car
                    $one_id = 'HY-SH-C-'.date("Ymd",time()).'-'.$addid;
                    $d_w['id'] = $addid;
                    $da['one_pay_id'] = $one_id;
                    $this->model->where($d_w)->update($da);

                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

}
