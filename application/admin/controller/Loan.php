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
class Loan extends Backend
{
    
    /**
     * Borrow模型对象
     * @var \app\admin\model\Borrow
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Borrow;
        $this->view->assign("moneyTypeList", $this->model->getMoneyTypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("borrowTypeList", $this->model->getBorrowType());

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
                    ->with(['admin','project','dictvalues','ptype'])
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);
//            dump($this->model->getLastSql());exit;


            //计算借支总
            $jz_ttl = $this->jz_ttl('',0);
            //计算借支抵扣金额
            $jz_dk = $this->jz_ttl('',1);
            //计算现阶段借支欠款
            $jz_qk = $jz_ttl-$jz_dk;
            $jz['jz_ttl'] = $jz_ttl;
            $jz['jz_dk'] = $jz_dk;
            $jz['jz_qk'] = $jz_qk;
            //计算备用金
            $byjttl = $this->byj('',0);
            $byj_gh = $this->byj('',1);
            $byjttl = $byjttl-$byj_gh;
            $byj['byj'] = $byjttl;
            foreach ($list as $row) {
                
                $row->getRelation('admin')->visible(['id','nickname','email']);
				$row->getRelation('project')->visible(['id','company','project']);
				$row->getRelation('dictvalues')->visible(['id','name','status']);
                $row->getRelation('ptype')->visible(['id','name']);

            }
            $result = array('byj'=>$byj,'jz' => $jz,"total" => $list->total(), "rows" => $list->items());
            return json($result);
        }
        return $this->view->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $params['adduser'] = $this->auth->id;
            $params['addtime'] = time();
            $params['money_type'] = 0;
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
                    //ID编码规则 HY-SH-B-20211012-126 城市 类型 借款类型 日期 ID号
                    $resid = $this->model->getLastInsID();
                    $addid = 'HY-SH-B-'.$params['borrow_type'].'-'.date("Ymd",time()).'-'.$resid;
                    $up['borrow_id'] = $addid;
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
                    $data['orders_id'] = $resid;
                    $data['check_status'] = null;
                    if($params['project_type_id'] == 128){
                        $check = $c->where('check_id',216)->find()->toArray();

                    }else{
                        $check = $c->where('check_id',215)->find()->toArray();
                    }
                    if($params['money'] < 30000){
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
                    $data['task_type_id'] = 215;
                    $data['pay_type_id'] = $params['matter_id'];
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
                            ->subject('《借支申请》审核提醒，审核单：'.$data['one_id'])
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
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
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
        $i = $this->model->get(['id'=>$ids])->toArray();


        $c_w['id'] = $i['task_id'];
        //审核人状态
        $tres = $T->where($c_w)->field('leder_status,money_status,king_status,order_status,boss_status')->find();

        if($tres){
            $tres = $tres->toArray();
            $bz = $T->where($c_w)->field('ps')->find()->toArray();
            $row['beizhua'] = $bz['ps'];
            $jindu = $T->get(['id' => $i['task_id']]);
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
        $ur = $U->where('id',$row['adduser'])->field('nickname')->find()->toArray();
        $pt = $D->where('id',$row['project_type_id'])->field('name')->find()->toArray();
        $pti = $D->where('id',$row['matter_id'])->field('name')->find()->toArray();
        $row['username'] = $ur['nickname'];
        $row['pname'] = $pn['project'];
        $row['ptname'] = $pt['name'];
        $row['payname'] = $pti['name'];
        $row['photo'] = explode(',', $row['photo_images']);
        $this->view->assign("jindu", $jindu);
        $this->view->assign("row", $row);
        $this->view->assign("check", $tres);

        return $this->view->fetch();
    }




    //计算借支总金额 $user 用户 $borrow_type 借支 备用金  金额正负 $money_type
    public function jz_ttl($user,$money_type){
        if($user != ''){
            $map['adduser'] = $user;

        }
        $map['borrow_type'] = 0;
        $map['status'] = 3;
        $map['money_type'] = $money_type;
        //计算借支总金额
        $ttl = $this->model->where($map)->sum('money');
        return $ttl;
    }
    //计算备用金金额
    public function byj($user,$money_type){
        if($user != ''){
            $map['adduser'] = $user;

        }
        $map['borrow_type'] = 1;
        $map['status'] = 3;
        $map['money_type'] = $money_type;
        $ttl = $this->model->where($map)->sum('money');
        return $ttl;
    }



}
