<?php

namespace app\admin\controller;

use app\admin\model\Check;
use app\admin\model\DictValues;
use app\admin\model\Task;
use app\common\controller\Backend;
use app\common\library\Email;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Claims extends Backend
{
    
    /**
     * Claims模型对象
     * @var \app\admin\model\Claims
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Claims;
        $this->view->assign("insuranceList", $this->model->getInsuranceList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("zerenm", $this->model->getZerenList());

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
                    ->with(['admin','project','supplier','dictvalues','zerena','pay','lipei','fgs'])
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);
            foreach ($list as $row) {
                
                $row->getRelation('admin')->visible(['id','nickname','status','dict_values_id']);
				$row->getRelation('project')->visible(['id','project','company']);
				$row->getRelation('supplier')->visible(['id','company']);
				$row->getRelation('dictvalues')->visible(['id','name','status']);

                $row->getRelation('pay')->visible(['id','name','status']);
                $row->getRelation('lipei')->visible(['id','name','status']);

                switch($row['zeren']){
                    case 0:
                        $row->getRelation('zerena')->visible(['id','company']);
                        break;
                    case 1:
                        $row->getRelation('fgs')->visible(['id','name','status']);
                        break;
                    case 4:
                        $row->getRelation('project')->visible(['id','project','company']);
                        break;
                }

            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $params['adduser'] = $this->auth->id;
            if($params['zeren'] != 0){
                $params['responsibility_id'] = 19890212;
            }
            if($params['zeren'] == 3){
                $params['dz_time'] = null;
            }else{
                $params['dz_time'] = time();
            }
            if ($params) {
                $params = $this->preExcludeFields($params);

                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                //查出项目名字 写入公司名
                if($params['zeren'] == 4){
//                    $P = new \app\admin\model\Project();
//                    $pc = $P->where('id',$params['project_id'])->field('company')->find()->toArray();
                    $params['fgs_id'] = $params['project_id'];
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
                    //ID编码规则 HY-SH-P-20211012-126 城市 类型 日期 ID号
                    $resid = $this->model->getLastInsID();
                    $addid = 'HY-SH-P-'.date("Ymd",time()).'-'.$resid;
                    $up['claims_id'] = $addid;
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
                        $check = $c->where('check_id',127)->find()->toArray();//索赔编号125

                    }else{
                        $check = $c->where('check_id',125)->find()->toArray();//索赔编号125
                    }

                    //ID编码规则 HY-SH-P-20211012-126 城市 类型 日期 ID号
                    $addid = 'HY-SH-P-'.date("Ymd",time()).'-'.$resid;
                    $data['orders_id'] = $resid;
                    $data['check_status'] = null;
                    //索赔都要过张总 0元只做记录，不参与审批流程和付款
                    $data['king_status'] = 1;//索赔不过张总 21年12月27更改
                    if($params['money'] == 0){
                        $data['order_status'] = 1;
                        $data['boss_status'] = 1;
                        $data['king_status'] = 1;
                        $data['money_status'] = 1;
                        $data['pay_status'] = 1;
                        $data['ps'] = '无需付款，只做记录。';
                        $data['check_status'] = 1;
                        //0元直接修改索赔表状态完成
                        $w['id'] = $this->model->id;
                        $this->model->where($w)->update(['status'=>1]);
                    }



                    $data['orders_id'] = $this->model->id;
                    $data['adduser'] = $this->auth->id;
                    $data['addtime'] = time();
                    $data['leder_id'] = $check['check_leder_id'];
                    $data['order_id'] = $check['check_order_id'];
                    $data['boss_id'] = $check['check_boss_id'];
                    $data['money_id'] = $check['check_money_id'];
                    $data['king_id'] = $check['check_king_id'];
                    $data['pay_id'] = $check['check_pay_id'];
                    $data['task_type_id'] = 125;
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
                            ->subject('《索赔》审核提醒，审核单：'.$data['one_id'])
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
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }

            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
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
                    $up['check_status'] = $check;
                    $up['ps'] = $beizhu;
                    if($check == 1 ){
                        // 处理任务表
                        $ww['task_id'] = $ids;
                        $cp = new \app\admin\model\Claims();
                        $upa['status'] = 5;
                        $upa['pay_time'] = time();
                        $cp->where($ww)->update($upa);
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
                        // 处理任务表
                        $ww['task_id'] = $ids;
                        $cp = new \app\admin\model\Claims();
                        $upa['status'] = 2;




                        $cp->where($ww)->update($upa);
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
                        $cp = new \app\admin\model\Claims();
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

//                        dump($emailuser);exit;
                        //如果状态为money审核 只改变carpay状态
                        if($new == 'money_status'){
                            $ww['task_id'] = $ids;
                            $cp = new \app\admin\model\Claims();
                            $upa['status'] = 3;
                            $cp->where($ww)->update($upa);

                        }

                        $task_id = $task->where('id',$id)->field($emailuser)->find();

                        $email = $ad->where('id',$task_id[$emailuser])->field('id,email')->find()->toArray();
                        //准备文字内容
                        $url = 'http://sys.dscloud.me:888/uBPmpTIrcF.php/task?ref=addtabs';
                        $txt = '您有一笔待审核业务单，请点击连接查看。<br/><a href="'.$url.'">点击访问</a>';


                        $mail
                            ->subject('《索赔》审核提醒，审核单：'.$trres['one_id'])
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
        $r =  $dv->where('id',125)->field('name')->find()->toArray();
        $pt =  $dv->where('id',$row['project_type_id'])->field('name')->find()->toArray();
        $p =  $pory->where('id',$row['project_id'])->field('project')->find()->toArray();
        $pti =  $dv->where('id',$row['pay_type_id'])->field('name')->find()->toArray();
        if($row['zeren'] == 1){
            $D = new DictValues();
            $fgs = $D->where('id',$row['fgs_id'])->field('name')->find()->toArray();
            $row['fgs'] = $fgs['name'];

        }


        if($row['responsibility_id'] != 19890212){
            $S = new \app\admin\model\Supplier();
            $cn = $S->where('id',$row['responsibility_id'])->field('company')->find();
            $row['cn'] = $cn['company'];
        }
//        if($row['responsibility_id'] != 0){
//            $spn =  $sp->where('id',$row['responsibility_id'])->field('company')->find()->toArray();
//            $row['zeren'] = $spn['company'];
//        }else{
//            $row['zeren'] = '123';
//        }
        $lipt =  $dv->where('id',$row['Claim_type_id'])->field('name')->find()->toArray();


        $row['img'] = explode(',', $row['photos']);
        $row['username'] = $ur['nickname'];
        $row['pay_type_name'] = $r['name'];
        $row['pname'] = $p['project'];
        $row['pt'] = $pt['name'];
        $row['fukuan'] = $pti['name'];
        $row['lplx'] = $lipt['name'];



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

    //定责方法
    public function dingze($ids){
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
                $cys_id = $params['cys_id'];
                $data['0'] = $params['cys_money'];
                $data['2'] = $params['sh_money'];
                $data['1'] = $params['other_money'];
                $data['4'] = $params['kh_money'];
                $data['photos-sh'] = $params['photos-sh'];
                $data['photos-cys'] = $params['photos-cys'];
                $data['photos-fgs'] = $params['photos-fgs'];
                $data['photos-kh'] = $params['photos-kh'];
                $checkttl = $params['cys_money']+$params['sh_money']+$params['other_money']+$params['kh_money'];
                $ttl = $params['ttlmoney'];
                $result = false;
                Db::startTrans();
                try {
                    $cys = $fgs = $sh = $kh = '';

                    //验证金额合法性
                    if($ttl != $checkttl){
                        $this->error('定责金额总额需与"申请总金额相符"');
                    }else{
                        //添加到索赔表，增加索赔记录
                        $C = new \app\admin\model\Claims();
                        $cR = $C->get($ids)->toArray();
                        foreach ($data as $k => $v){
                            if($v != 0){
                                //不同类型sql不同
                                if($k == 0){
                                    //承运商承担
                                    $d['claims_id'] = $params['claims_id'].'-'.$k;
                                    $d['dingdan_id'] = $cR['dingdan_id'];
                                    $d['project_id'] = $cR['project_id'];
                                    $d['project_type_id'] = $cR['project_type_id'];
                                    $d['zeren'] = $k;
                                    $d['responsibility_id'] = $cys_id;//承运商id
                                    $d['pay_type_id'] = $cR['pay_type_id'];//承运商id
                                    $d['Claim_type_id'] = $cR['Claim_type_id'];//
                                    $d['date'] = $cR['date'];//承运商id
                                    $d['money'] = $v;
                                    $d['insurance'] = $cR['insurance'];
                                    $d['payee'] = $cR['payee'];
                                    $d['number'] = $cR['number'];
                                    $d['invoice_id'] = $cR['invoice_id'];
                                    $d['photos'] = $params['photos-cys'];
                                    $d['ps'] = '主订单号:《'.$params['claims_id'].'》总索赔'.$params['ttlmoney'].'元,承运商承担'.$v.'元.';
                                    $cys = '承运商承担'.$v.'元.';
                                    $d['adduser'] = $this->auth->id;
                                    $d['createtime'] = $cR['createtime'];;
                                    $d['dz_time'] = time();
                                    $d['status'] = 0;
                                    $oneid = $d['claims_id'];
                                    $i_id = $C->insertGetId($d);
                                    $this->do_dingze($params,$i_id,$oneid);
                                    //写入任务表
                                }else{
                                    //其他只定责，写入金额，不需要承运商id
                                    $d['claims_id'] = $params['claims_id'].'-'.$k;
                                    $d['dingdan_id'] = $cR['dingdan_id'];
                                    $d['project_id'] = $cR['project_id'];
                                    $d['project_type_id'] = $cR['project_type_id'];
                                    $d['zeren'] = $k;
                                    $d['fgs_id'] = null;
                                    switch($k){
                                        case 1:
                                            $d['fgs_id'] = $params['fgs_id'];
                                            $d['photos'] = $params['photos-fgs'];
                                        break;
                                        case 2:
                                            $d['photos'] = $params['photos-sh'];
                                            break;
                                        case 4:
                                            $d['photos'] = $params['photos-kh'];
                                            break;

                                    }
                                    $d['responsibility_id'] = 19890212;//承运商id
                                    $d['pay_type_id'] = $cR['pay_type_id'];//承运商id
                                    $d['Claim_type_id'] = $cR['Claim_type_id'];//
                                    $d['date'] = $cR['date'];//承运商id
                                    $d['money'] = $v;
                                    $d['insurance'] = $cR['insurance'];
                                    $d['payee'] = $cR['payee'];
                                    $d['number'] = $cR['number'];
                                    $d['invoice_id'] = $cR['invoice_id'];

                                    switch($k){
                                        case 1:
                                            $d['ps'] = '主订单号:《'.$params['claims_id'].'》总索赔'.$params['ttlmoney'].'元,其他分公司承担'.$v.'元.';
                                            $fgs = '其他分公司承担'.$v.'元。';
                                            break;
                                        case 2:
                                            $d['ps'] = '主订单号:《'.$params['claims_id'].'》总索赔'.$params['ttlmoney'].'元,上海分公司承担'.$v.'元.';
                                            $sh = '其他分公司承担'.$v.'元。';
                                            break;
                                        case 4:
                                            $d['ps'] = '主订单号:《'.$params['claims_id'].'》总索赔'.$params['ttlmoney'].'元,客户承担'.$v.'元.';
                                            $kh = '其他分公司承担'.$v.'元。';
                                            $fgs = '其他分公司承担'.$v.'元。';
                                            break;
                                    }
                                    $d['adduser'] = $this->auth->id;
                                    $d['createtime'] = $cR['createtime'];;
                                    $d['dz_time'] = time();
                                    $d['status'] = 0;
                                    $oneid = $d['claims_id'];
                                    $i_id = $C->insertGetId($d);
                                    $result = $this->do_dingze($params,$i_id,$oneid);
                                }
                            }
                        }
                        //修改原始订单金额
                        $D['money'] = 0;//清零主订单金额
                        $D['dz_time'] = time();//主订单定责时间
                        $D['zeren'] = 5;//已定责
                        $C_res = $C->where('id',$ids)->field('ps')->find();
                        $C_res = $C_res->toArray();
                        $DPS = $C_res['ps'];
                        $text = '主订单号:《'.$params['claims_id'].'》总索赔'.$params['ttlmoney'].'元,'.$cys.$fgs.$sh.$kh;
                        $D['ps'] = $DPS.'系统备注:'.$text;//备注主订单内容
                        $W['id'] = $ids;
                        $C->where($W)->update($D);
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
        $S = new \app\admin\model\Supplier();
        //查询具体taskid
        $type = $T->where('id',$row['task_id'])->field('task_type_id')->find()->toArray();
        switch($type['task_type_id']){
            case 129:
                $c_w['task_type_id'] = 129;
                break;
            case 125:
                $c_w['task_type_id'] = 125;
                break;
            case 130:
                $c_w['task_type_id'] = 130;
                break;
        }

        //限定为索赔
        $c_w['orders_id'] = $ids;
        //审核人状态
        $tres = $T->where($c_w)->field('leder_status,money_status,king_status,order_status,boss_status')->find()->toArray();

        $pn = $P->where('id',$row['project_id'])->field('project')->find()->toArray();
        $ur = $U->where('id',$row['adduser'])->field('nickname')->find()->toArray();
        $pt = $D->where('id',$row['project_type_id'])->field('name')->find()->toArray();
        $pti = $D->where('id',$row['pay_type_id'])->field('name')->find()->toArray();
        $cti = $D->where('id',$row['Claim_type_id'])->field('name')->find()->toArray();
        if($row['zeren'] == 1){
            $fgs = $D->where('id',$row['fgs_id'])->field('name')->find()->toArray();
            $row['fgs'] = $fgs['name'];

        }

        $bz = $T->where($c_w)->field('ps')->find()->toArray();
        if($row['responsibility_id'] != 19890212){
            $cn = $S->where('id',$row['responsibility_id'])->field('company')->find()->toArray();
            $row['cn'] = $cn['company'];
        }


        $row['username'] = $ur['nickname'];
        $row['pname'] = $pn['project'];
        $row['ptname'] = $pt['name'];
        $row['ptiname'] = $pti['name'];
        $row['ctn'] = $cti['name'];
        $row['beizhu'] = $bz['ps'];



        $row['photo'] = explode(',', $row['photos']);

        //查进度
        //查出taskid


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

        $this->view->assign("row", $row);
        $this->view->assign("check", $tres);
        $this->view->assign("jindu", $jindu);
        return $this->view->fetch();
    }

    //定责添加任务方法
    public function do_dingze($params,$i_id,$oneid){
        $c = new Check();
        $t = new Task();
        //专线非专线审核有区别
        if($params['project_type_id'] == 128){
            $check = $c->where('check_id',130)->find()->toArray();//定责审核130 专线
            $data['task_type_id'] = 130;
        }else{
            $check = $c->where('check_id',129)->find()->toArray();//定责审核129 专线
            $data['task_type_id'] = 129;
        }

        //ID编码规则 HY-SH-P-20211012-126 城市 类型 日期 ID号-子订单号
        $data['orders_id'] = $i_id;
        $data['check_status'] = null;
        //索赔都要过张总 0元只做记录，不参与审批流程和付款

        $data['adduser'] = $this->auth->id;
        $data['addtime'] = time();
        $data['leder_id'] = $check['check_leder_id'];
        $data['order_id'] = $check['check_order_id'];
        $data['boss_id'] = $check['check_boss_id'];
        $data['money_id'] = $check['check_money_id'];
        $data['king_id'] = $check['check_king_id'];
        $data['pay_id'] = $check['check_pay_id'];
        $data['pay_type_id'] = $params['pay_type_id'];
        $data['one_id'] = $oneid;
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
                ->subject('《索赔》定责审核提醒，审核单：'.$oneid)
                ->from('system@huayuanl.com','Information')
                ->to($email['email'])
                ->message($txt,$ishtml = true)
                ->send();

        }
        if($check['check_king_id'] == 24){
            $data['king_status'] = 1;
            $data['king_time'] = time();
        }
        $r = $t->insertGetId($data);
        $w['id'] = $i_id;
        $result = $this->model->where($w)->update(['task_id'=>$r]);
        return $result;
    }
}
