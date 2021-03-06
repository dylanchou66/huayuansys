<?php

namespace app\admin\controller;

use app\admin\model\Check;
use app\admin\model\DictValues;
use app\admin\model\Task;
use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use app\common\library\Email;


/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Pay extends Backend
{
    
    /**
     * Pay模型对象
     * @var \app\admin\model\Pay
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Pay;
        $this->view->assign("typeIdList", $this->model->getTypeIdList());
        $this->view->assign("checkIdList", $this->model->getCheckIdList());
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
                    ->with(['dictvalues','project','peytype','adduser','fgs'])
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);

            foreach ($list as $row) {
                
                $row->getRelation('dictvalues')->visible(['id','name','status']);
                $row->getRelation('peytype')->visible(['id','name','status']);
                $row->getRelation('adduser')->visible(['id','nickname','status']);
                $row->getRelation('fgs')->visible(['id','name','status']);



            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    public function add()
    {
        //获取个人欠款
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

                    //如果核销 写入明细
                    $params['borrow'] = isset($params['borrow'])?$params['borrow']:0;
                    if($params['borrow'] != 1){
                        unset($params['borrow_money']);
                        unset($params['cash']);
                        unset($params['jz_beizhu']);
                    }
                    $params['jz_beizhu'] = isset($params['jz_beizhu'])?$params['jz_beizhu']:"";
                    //成本指出不需要确认金额
                    if($params['type_id'] == 1){
                        $params['df_type'] = 0;
                        $params['df_id'] = 46;
                        $params['check_id'] = 1;
                        $params['check_user'] = $this->auth->id;
                        $params['check_time'] = time();
                        $params['check_photo'] = $params['photo_images'];
                    }
                    $params['beizhu'] = $params['beizhu'].$params['jz_beizhu'];


                    $result = $this->model->allowField(true)->save($params);
                    //ID编码规则 HY-SH-Y-20211012-126 城市 类型 日期 ID号
                    $resid = $this->model->getLastInsID();
                    $addid = 'HY-SH-Y-'.date("Ymd",time()).'-'.$resid;
                    $up['pay_one_id'] = $addid;
                    $w['id'] = $resid;
                    $this->model->where($w)->update($up);
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
                    $c = new Check();
                    $t = new Task();
                    if($params['project_type_id'] == 128){
                        $check = $c->where('check_id',157)->find()->toArray();//

                    }else{
                        $check = $c->where('check_id',156)->find()->toArray();
                    }

                    //ID编码规则 HY-SH-P-20211012-126 城市 类型 日期 ID号

                    $data['orders_id'] = $resid;
                    $data['check_status'] = null;
                    if($params['pay_money'] < 30000){
                        $data['king_status'] = 1;
                    }

                    $data['adduser'] = $this->auth->id;
                    $data['addtime'] = time();
                    $data['leder_id'] = $check['check_leder_id'];
                    $data['order_id'] = $check['check_order_id'];
                    $data['boss_id'] = $check['check_boss_id'];
                    $data['money_id'] = $check['check_money_id'];
                    $data['king_id'] = $check['check_king_id'];
                    $data['pay_id'] = $check['check_pay_id'];
                    $data['task_type_id'] = 158;
                    $data['pay_type_id'] = $params['pay_type_id'];
                    $data['one_id'] = $addid;
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
                            ->subject('《付费》审核提醒，审核单：'.$data['one_id'])
                            ->from('system@huayuanl.com','Information')
                            ->to($email['email'])
                            ->message($txt,$ishtml = true)
                            ->send();

                    }
                    if($check['check_order_id'] == 24){
                        $data['order_status'] = 1;
                        $data['order_time'] = time();
                    }
                    $r = $t->insertGetId($data);
                    $w['id'] = $this->model->id;
                    $result = $this->model->where($w)->update(['task_id'=>$r]);
                    if($result != 1){
                        $this->error('网络错误，请联系管理员');
                    }
                    //存入借支表
                    if($params['borrow'] == 1){
                        $B = new \app\admin\model\Borrow();
                        $r = $B->add($this->auth->id,$params,$resid,$r,$addid);
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
    public function one($ids){
        $row = $this->model->get(['id' => $ids]);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $row = $row->toArray();
        $T = new Task();
        $D = new DictValues();
        $U = new \app\admin\model\Admin();
        $P = new \app\admin\model\Project();
        //查询具体taskid
//        $type = $T->where('id',$row['task_id'])->field('task_type_id')->find()->toArray();


        //限定为付费
        $c_w['task_type_id'] = 158;
        $c_w['orders_id'] = $ids;
        //审核人状态
        $tres = $T->where($c_w)->field('leder_status,money_status,king_status,order_status,boss_status')->find();
        if($tres){
            $tres = $tres->toArray();
            $bz = $T->where($c_w)->field('ps')->find()->toArray();
            $row['beizhua'] = $bz['ps'];

            $jindu = $T->where($c_w)->find()->toArray();
            $lur = $U->where('id',$jindu['leder_id'])->field('nickname')->find()->toArray();
            $our = $U->where('id',$jindu['order_id'])->field('nickname')->find()->toArray();
            $bur = $U->where('id',$jindu['boss_id'])->field('nickname')->find()->toArray();
            $mur = $U->where('id',$jindu['money_id'])->field('nickname')->find()->toArray();
            if($jindu['king_id'] != ''){
                $kur = $U->where('id',$jindu['king_id'])->field('nickname')->find()->toArray();
                $jindu['kur'] = $kur['nickname'];

            }
            $jindu['lur'] = $lur['nickname'];
            $jindu['our'] = $our['nickname'];
            $jindu['bur'] = $bur['nickname'];
            $jindu['mur'] = $mur['nickname'];


        }else{
            $jindu = '';
        }
        $pn = $P->where('id',$row['project_id'])->field('project')->find()->toArray();
        $pc =  $P->where('id',$row['project_id'])->field('company')->find()->toArray();
        $row['pc'] = $pc['company'];

        $row['pname'] = $pn['project'];

        $ur = $U->where('id',$row['adduser'])->field('nickname')->find()->toArray();
        $pt = $D->where('id',$row['project_type_id'])->field('name')->find()->toArray();
        $pti = $D->where('id',$row['pay_type_id'])->field('name')->find()->toArray();
        if($row['df_type'] == 1){
            $fgs = $D->where('id',$row['df_id'])->field('name')->find()->toArray();
            $row['fgsn'] = $fgs['name'];
        }
//        $cti = $D->where('id',$row['Claim_type_id'])->field('name')->find()->toArray();


        $row['username'] = $ur['nickname'];
        $row['ptname'] = $pt['name'];
        $row['payname'] = $pti['name'];




        $row['photo'] = explode(',', $row['photo_images']);

        //查进度
        //查出taskid


        $this->view->assign("jindu", $jindu);
        $this->view->assign("row", $row);
        $this->view->assign("check", $tres);

        return $this->view->fetch();
    }


    //查看单条数据
    public function detail($ids)
    {
        $row = $this->model->get(['task_id' => $ids]);
        if (!$row) {
            $this->error(__('No Results were found'));
        }

        $cp = new \app\admin\model\Task();
        $T = new \app\admin\model\Task();
        $dv = new DictValues();
        $pory = new \app\admin\model\Project();
        $user = new \app\admin\model\Admin();
        $sp = new \app\admin\model\Supplier();
        $row = empty($row) ? array():$row->toArray();
        //先查出审核数据
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
            $id = $b['ids'];
            $check = $b['check'];
//                $nid = $T->where('orders_id',$id)->field('id')->find();
            //先查出审核数据
            $trres = $T->where('id',$id)->field(array('adduser','king_id','leder_id','order_id','boss_id','money_id','pay_id','one_id'))->find();

            //检查是否正常流程
            $checkres = array_search($this->auth->id,$trres->toArray());

            if(!$checkres or $check == null or $check == ''){
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
                if($new == 'pay_status'){
                    //先确认是否存在借支
                    $bi = $this->model->get(['task_id'=>$id])->toArray();
                    $up['check_status'] = $check;
                    $up['ps'] = $beizhu;
                    if($check == 1 or $check ==4){
                        // 处理任务表
                        $ww['task_id'] = $ids;
                        $cp = new \app\admin\model\Pay();
                        $upa['status'] = 5;
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

//                         dump($cp->getLastSql());exit;

                    }else{
                        // 处理任务表
                        $ww['task_id'] = $ids;
                        $cp = new \app\admin\model\Pay();
                        $upa['status'] = 2;
                        $cp->where($ww)->update($upa);
                        //借支确认
                        if($bi['borrow'] == 1){
                            $B = new \app\admin\model\Borrow();
                            $m['gl_order_id'] = $bi['id'];
                            $u['status'] = 2;
                            $B->where($m)->update($u);
                        }
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
                        $cp = new \app\admin\model\Pay();
                        $cp->where('task_id',$id)->update($cpup);
                    }else{
                        $ad = new \app\admin\model\Admin();
                        $task = new Task();
                        //查下个用户邮箱
                        switch($new){
                            case 'leder_status':
                                //判断是否审核过
                                $check_res = $task->where('id',$id)->field('order_id,order_status')->find()->toArray();
                                if($check_res['order_status'] == null){
                                    if($check_res['order_id'] == 24){
                                        $emailuser = 'boss_id';
                                    }else{
                                        $emailuser = 'order_id';
                                    }
                                }else{
                                    $emailuser = 'boss_id';
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
                                $ww['task_id'] = $ids;
                                $cp = new \app\admin\model\pay();
                                $upa['status'] = 1;
                                $r = $cp->where($ww)->update($upa);
                                $check_res = $task->where('id',$id)->field('pay_id,pay_status')->find()->toArray();
                                if($check_res['pay_status'] == null){
                                    $emailuser = 'pay_id';
                                }
                                break;
                            default:
                                $this->error('网络错误');
                                break;
                        }


                        $task = new Task();

//                        //如果状态为money审核 只改变carpay状态
//                        if($new == 'money_status'){
//                            $ww['task_id'] = $ids;
//                            $cp = new \app\admin\model\pay();
//                            $upa['status'] = 1;
//                            $r = $cp->where($ww)->update($upa);
//                        }

                        $task_id = $task->where('id',$id)->field($emailuser)->find();
                        $email = $ad->where('id',$task_id[$emailuser])->field('id,email')->find()->toArray();
                        //准备文字内容
                        $url = 'http://sys.dscloud.me:888/uBPmpTIrcF.php/task?ref=addtabs';
                        $txt = '您有一笔待审核业务单，请点击连接查看。<br/><a href="'.$url.'">点击访问</a>';


                        $mail
                            ->subject('《付款》审核提醒，审核单：'.$trres['one_id'])
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

        $ur = $user->where('id',$row['adduser'])->field('nickname')->find()->toArray();
        $r =  $dv->where('id',158)->field('name')->find()->toArray();
        $pt =  $dv->where('id',$row['project_type_id'])->field('name')->find()->toArray();
        $p =  $pory->where('id',$row['project_id'])->field('project')->find()->toArray();
        $p =  $pory->where('id',$row['project_id'])->field('project')->find()->toArray();
        $pc =  $pory->where('id',$row['project_id'])->field('company')->find()->toArray();
        $row['pname'] = $p['project'];

        $row['pc'] = $pc['company'];


        $pti =  $dv->where('id',$row['pay_type_id'])->field('name')->find()->toArray();
        $pt = $dv->where('id',$row['project_type_id'])->field('name')->find()->toArray();
        $pti = $dv->where('id',$row['pay_type_id'])->field('name')->find()->toArray();
        $c_w['task_type_id'] = 158;
        $c_w['id'] = $ids;
        $bz = $T->where($c_w)->field('ps')->find()->toArray();
        $row['beizhua'] = $bz['ps'];
        $row['payname'] = $pti['name'];
        $row['ptname'] = $pt['name'];



        $row['img'] = explode(',', $row['photo_images']);
        $row['username'] = $ur['nickname'];
        $row['pay_type_name'] = $r['name'];
        $row['pt'] = $pt['name'];
        $row['fukuan'] = $pti['name'];

        $row['photo'] = explode(',', $row['photo_images']);


        //查进度
        //查出taskid
        $jindu = $cp->get(['id' => $ids]);
        $lur = $user->where('id',$jindu['leder_id'])->field('nickname')->find()->toArray();
        $our = $user->where('id',$jindu['order_id'])->field('nickname')->find()->toArray();
        $bur = $user->where('id',$jindu['boss_id'])->field('nickname')->find()->toArray();
        $mur = $user->where('id',$jindu['money_id'])->field('nickname')->find()->toArray();
        if($jindu['king_id'] != ''){
            $kur = $user->where('id',$jindu['king_id'])->field('nickname')->find()->toArray();
            $jindu['kur'] = $kur['nickname'];

        }


        $jindu['lur'] = $lur['nickname'];
        $jindu['our'] = $our['nickname'];
        $jindu['bur'] = $bur['nickname'];
        $jindu['mur'] = $mur['nickname'];




        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if ($this->request->isAjax()) {
            $this->success("Ajax请求成功", null, ['id' => $ids]);
        }
        $this->view->assign("row", $row);
        $this->view->assign("new", $new);

        $this->view->assign("jindu", $jindu->toArray());
        return $this->view->fetch();

    }


    //确认金额
    public function check($ids)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost($ids)) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                //先验证金额是否合法 0=承运商,1=其他分公司,2=上海分公司,3=未定责,4=客户
                $pay_id = $params['pay_one_id'];
                $data['0'] = $params['sh_money'];
                $data['1'] = $params['fgs_money'];
                $data['2'] = $params['kh_money'];
                $checkttl = $params['sh_money']+$params['fgs_money']+$params['kh_money'];
                $ttl = $params['pay_money'];
                $result = false;
                Db::startTrans();
                try {

                    //验证金额合法性
                    if($checkttl < $ttl){
                        $this->error("请重新确认金额");
                    }else{
                        $sh = $fgs = $kh = '';
                        //添加到索赔表，增加索赔记录
                        $P = $this->model;
                        $cR = $P->get($ids)->toArray();
                        foreach ($data as $k => $v){
                            if($v != 0){
                                //承运商承担
                                $d['pay_one_id'] = $cR['pay_one_id'].'-'.$k;
                                $d['cmm_id'] = $cR['cmm_id'];
                                $d['dh_id'] = $cR['dh_id'];
                                $d['project_type_id'] = $cR['project_type_id'];
                                $d['project_id'] = $cR['project_id'];
                                $d['pay_type_id'] = $cR['pay_type_id'];
                                switch($k){
                                    case 0:
                                        $d['type_id'] = 1;
                                        $d['df_id'] = 46;
                                        $d['df_type'] = 0;
                                        $d['beizhu'] = '主订单号:《'.$params['pay_one_id'].'》总金额'.$params['pay_money'].'元,上海分公司成本支出'.$v.'元.';
                                        $sh = '上海分公司成本支出'.$v.'元.';
                                        break;
                                    case 1:
                                        $d['type_id'] = 0;
                                        $d['df_id'] = $params['fgs_id'];
                                        $d['df_type'] = 1;
                                        $D = new \app\admin\model\DictV();
                                        $fgsname = $D->where('id',$params['fgs_id'])->field('name')->find()->toArray();
                                        $d['beizhu'] = '主订单号:《'.$params['pay_one_id'].'》总金额'.$params['pay_money'].'元，为'.$fgsname['name'].'支出'.$v.'元.';
                                        $fgs = '为'.$fgsname['name'].'垫付'.$v.'元.';
                                        break;
                                    case 2:
                                        $d['type_id'] = 0;
                                        $d['df_id'] = $cR['project_id'];
                                        $d['df_type'] = 2;
                                        $Pro = new \app\admin\model\Project();
                                        $khname = $Pro->where('id',$cR['project_id'])->field('company')->find()->toArray();
                                        $d['beizhu'] = '主订单号:《'.$params['pay_one_id'].'》总金额'.$params['pay_money'].'元,为'.$khname['company'].'支出'.$v.'元.';
                                        $kh = '为'.$khname['company'].'垫付'.$v.'元.';
                                        break;
                                }
                                $d['photo_images'] = $params['check_photo'];
                                $d['newtime'] = $cR['newtime'];
                                $d['pay_money'] = $v;
                                $d['check_id'] = 1;
                                $d['check_photo'] = $params['check_photo'];
                                $d['check_user'] = $this->auth->id;
                                $d['check_time'] = time();
                                $d['user'] = $cR['user'];
                                $d['nember'] = $cR['nember'];
                                $d['invoice_id'] = $cR['invoice_id'];
                                $d['adduser'] = $this->auth->id;
                                $d['createtime'] = time();
                                $d['task_id'] = $cR['task_id'];
                                $d['status'] = 5;



                                    $i_id = $P->insertGetId($d);

                            }
                        }
                        //修改原始订单金额
                        $Dd['pay_money'] = 0;//清零主订单金额
                        $Dd['check_id'] = 1;//已确认


                        $C_res = $P->where('id',$ids)->field('beizhu')->find();
                        $C_res = $C_res->toArray();
                        $DPS = $C_res['beizhu'];
                        $text = '主订单号:《'.$params['pay_one_id'].'》总金额'.$params['pay_money'].'元,'.$sh.$fgs.$kh;
                        $Dd['beizhu'] = $DPS.'系统备注:'.$text;//备注主订单内容
                        $W['id'] = $ids;
                        $result = $P->where($W)->update($Dd);

                    }
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
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        $this->view->assign("row", $row);
        return $this->view->fetch();
    }


}
