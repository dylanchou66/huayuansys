<?php

namespace app\admin\controller;

use app\admin\model\Check;
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
class Card extends Backend
{
    
    /**
     * Card模型对象
     * @var \app\admin\model\Card
     */
    protected $model = null;
    protected $noNeedRight = ['jq','bind','info','check'];
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Card;
        $this->view->assign("cardTypeList", $this->model->getCardTypeList());
        $this->view->assign("cardMasterList", $this->model->getCardMasterList());
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
                    ->with(['admin','dictv'])
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);


            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    //卡充值
    public function recharge(){
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
                    //先写入账单表（bill)
                    $B = new \app\admin\model\Bill();
                    $b = new Bill();
                    $youkayue = $b->yue(5);
                    //查询充值卡的类型
                    $card_type = $this->model->where('id',$params['card_id'])->field('card_type')->find()->toArray();
                    $data['card_id'] = $params['card_id'];
                    $data['card_bill_type'] = $card_type['card_type'];
                    $data['adduser'] = $params['adduser'];
                    $data['money'] = $params['money'];
                    $data['money_type'] = 0;
                    $data['tmp_money'] = $youkayue;
                    $data['addtime'] = time();
                    $data['yw_id'] = 0;//充值业务id为0
                    $data['yw_type'] = 0;//充值业务类型为0
                    $data['ps'] = '充值申请';
                    $data['status'] = 0;
                    $result = $B->allowField(true)->save($data);
                    //ID编码规则 HY-SH-R-20211012-126 城市 类型 日期 ID号
                    $resid = $B->getLastInsID();
                    $addid = 'HY-SH-R-'.date("Ymd",time()).'-'.$resid;
                    $up['bill_id'] = $addid;
                    $w['id'] = $resid;
                    $B->where($w)->update($up);
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
                    $check = $c->where('check_id',247)->find()->toArray();

                    //ID编码规则 HY-SH-P-20211012-126 城市 类型 日期 ID号

                    $da['orders_id'] = $resid;
                    $da['check_status'] = null;
                    $da['king_status'] = 1;
                    $da['leder_status'] = 1;
                    $da['adduser'] = $this->auth->id;
                    $da['addtime'] = time();
                    $da['leder_id'] = $check['check_leder_id'];
                    $da['order_id'] = $check['check_order_id'];
                    $da['boss_id'] = $check['check_boss_id'];
                    $da['money_id'] = $check['check_money_id'];
                    $da['king_id'] = $check['check_king_id'];
                    $da['pay_id'] = $check['check_pay_id'];
                    $da['task_type_id'] = 247;
                    $da['pay_type_id'] = 247;
                    $da['one_id'] = $addid;
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
                            ->subject('《充值申请》审核提醒，审核单：'.$da['one_id'])
                            ->from('system@huayuanl.com','Information')
                            ->to($email['email'])
                            ->message($txt,$ishtml = true)
                            ->send();

                    }
                    if($check['check_order_id'] == 24){
                        $da['order_status'] = 1;
                        $da['order_time'] = time();
                    }
                    $r = $t->insertGetId($da);
                    $w['id'] = $resid;
                    $result = $B->where($w)->update(['task_id'=>$r]);
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



    //检查数据是否重复 油卡限定一人用
    public function check()
    {
        $name = $this->request->post();
        $map['useuser'] = $name['row']['useuser'];
        $count = $this->model->where($map)->count();
        if ($count > 0) {
            $this->error(__('重复领用'));
        }
        $this->success();
    }
    //查询司机绑定卡信息
    public function info(){
        $card_user = input('card_user');
        $map['useuser'] = $card_user;
        $map['status'] = 0;
        $res = $this->model->where($map)->find();
        if($res){
            $res = $res->toArray();
            //查卡余额
            $B = new Bill();
            $result = $B->yue($res['id']);
            $res['yue'] = $result;
            return $res;
        }else{
            return false;
        }
    }
    //查加气站余额
    public function jq(){
        $jqz = input('z_id');
        $map['card_type'] = 1;
        $map['status'] = 0;
        $map['card_master'] = 1;
        $map['id'] = $jqz;
        $res = $this->model->where($map)->find();
        if($res){
            $res = $res->toArray();
            return($res);
        }else{
            return false;
        }
    }

    //卡分配
    public function distribute(){
        //追加操作查询
        $map['status'] = 0;
        $map['card_master'] = 0;
        $categoryarr = $this->model->where($map)->field('id,card_number')->select()->toArray();
        $categoryarr = array_column($categoryarr, 'card_number','id');

        //查询主卡余额
        $B = new Bill();
        $youkayue = $B->yue(5);
        $this->assign('master_yue',$youkayue);
        $this->assign('category',$categoryarr);

        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                $result = false;
                //处理主卡扣款
                $info['card_name'] = '卡号';
                $info['ttlmoney'] = 0;
                foreach ($params as $value){
                    $card_name = $this->model->where('id',$value['id'])->field('card_number')->find()->toArray();
                    $info['ttlmoney'] += $value['money'];
                    $info['card_name'] .= "、".$card_name['card_number'].',分配'.$value['money'].'元';
                }
                $info['info'] = $info['card_name'].',合计分配'.$info['ttlmoney'].'元。';
                //先查询主卡是否够扣
                $B = new Bill();
                $master_yue = $B->yue(5);
                $res = $master_yue - $info['ttlmoney'];
                if($res >= 0){
                    $Bill = new \app\admin\model\Bill();
                    //主卡扣款记录
                    $master_data['card_id'] = 5;
                    $master_data['card_bill_type'] = 0;
                    $master_data['adduser'] = $this->auth->id;
                    $master_data['money'] = $info['ttlmoney'];
                    $master_data['money_type'] = 1;
                    $master_data['addtime'] = time();
                    $master_data['yw_id'] = 0;//充值业务id为0
                    $master_data['yw_type'] = 1989;//充值业务类型为0
                    $master_data['ps'] = '系统备注：'.$info['info'];
                    $master_data['task_id'] = null;
                    $master_data['status'] = 3;
                    $master_data['createtime'] = time();
                    $master_data['pay_time'] = time();
                    $result = $Bill->insertGetId($master_data);

                    $addid = 'HY-SH-F-'.date("Ymd",time()).'-'.$result;
                    $up['bill_id'] = $addid;
                    $w['id'] = $result;
                    $Bill->where($w)->update($up);
                    //开始卡分配操作
                    $tt = 0;
                    foreach($params as $k => $val){
                        $data['card_id'] = $val['id'];
                        $data['card_bill_type'] = 0;
                        $data['adduser'] = $this->auth->id;
                        $data['money'] = $val['money'];
                        $data['money_type'] = 0;
                        $data['addtime'] = time();
                        $data['yw_id'] = 0;//充值业务id为0
                        $data['yw_type'] = 1989;//充值业务类型为0
                        $data['ps'] = '系统备注:卡分配';
                        $data['task_id'] = null;
                        $data['status'] = 3;
                        $data['createtime'] = time();
                        $data['pay_time'] = time();
                        $result = $Bill->insertGetId($data);
                        $addid = 'HY-SH-F-'.date("Ymd",time()).'-'.$result;
                        $up['bill_id'] = $addid;
                        $w['id'] = $result;
                        $tt += $val['money'];
                        $Bill->where($w)->update($up);
                    }
                }else{
                    $this->error('主卡余额不足');
                }



                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }

            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }
    //检查是否绑定卡
    public function bind(){
        $name = $this->request->post();
        $map['useuser'] = $name['row']['siji_id'];
        $count = $this->model->where($map)->count();
        if ($count == 0) {
            $this->error(__('未绑定卡'));
        }
        $this->success();
    }
}
