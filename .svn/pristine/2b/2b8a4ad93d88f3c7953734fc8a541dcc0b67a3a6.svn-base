<?php

namespace app\admin\model;

use think\Model;


class Bill extends Model
{

    

    

    // 表名
    protected $name = 'bill';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'card_bill_type_text',
        'money_type_text',
        'addtime_text',
        'status_text',
        'pay_time_text'
    ];
    

    
    public function getCardBillTypeList()
    {
        return ['0' => __('Card_bill_type 0'), '1' => __('Card_bill_type 1')];
    }

    public function getMoneyTypeList()
    {
        return ['0' => __('Money_type 0'), '1' => __('Money_type 1')];
    }

    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1'), '2' => __('Status 2'), '3' => __('Status 3')];
    }


    public function getCardBillTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['card_bill_type']) ? $data['card_bill_type'] : '');
        $list = $this->getCardBillTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getMoneyTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['money_type']) ? $data['money_type'] : '');
        $list = $this->getMoneyTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getAddtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['addtime']) ? $data['addtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getPayTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['pay_time']) ? $data['pay_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setAddtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setPayTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function admin()
    {
        return $this->belongsTo('Admin', 'adduser', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function card()
    {
        return $this->belongsTo('Card', 'card_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    //卡扣款写入表
    public function bill_add($user,$params,$resid,$r,$addid){
        $b = new \app\admin\controller\Bill();
        $youkayue = $b->yue($params['card_id']);
        $bill['card_id'] = $params['card_id'];
        $C = new Card();
        $card_bill_type = $C->where('id',$params['card_id'])->field('card_type')->find()->toArray();
        $bill['card_bill_type'] = $card_bill_type['card_type'];
        $bill['adduser'] = $user;
        $bill['money'] = $params['money'];
        $bill['tmp_money'] = $youkayue;
        $bill['money_type'] = 1;
        $bill['addtime'] = time();
        $bill['yw_type'] = $params['pay_type_id'];
        $bill['task_id'] = $r;
        $bill['status'] = 0;
        $bill['ps'] = '信息：'.$params['ps'].'关联单号:'.$addid;
        $bill['createtime'] = time();
        $bill['yw_id'] = $resid;
        $this->insert($bill);
        $resid = $this->getLastInsID();
        $addid = 'HY-SH-Z-'.date("Ymd",time()).'-'.$resid;
        $up['bill_id'] = $addid;
        $w['yw_id'] = $bill['yw_id'];
        $result = $this->where($w)->update($up);
        return $result;
    }
    //卡扣款写入表 合同
    public function bill_add_hetong($user,$params,$resid,$r,$addid){
        $b = new \app\admin\controller\Bill();
        $youkayue = $b->yue($params['card_id']);
        $bill['card_id'] = $params['card_id'];
        $C = new Card();
        $card_bill_type = $C->where('id',$params['card_id'])->field('card_type')->find()->toArray();
        $bill['card_bill_type'] = $card_bill_type['card_type'];
        $bill['adduser'] = $user;
        $bill['money'] = $params['youka'];
        $bill['tmp_money'] = $youkayue;
        $bill['money_type'] = 1;
        $bill['addtime'] = time();
        $bill['yw_type'] = $params['pay_type_id'];
        $bill['task_id'] = $r;
        $bill['status'] = 0;
        $bill['ps'] = '信息：'.$params['ps'].'关联单号:'.$addid;
        $bill['createtime'] = time();
        $bill['yw_id'] = $resid;
        $this->insert($bill);
        $resid = $this->getLastInsID();
        $addid = 'HY-SH-Z-'.date("Ymd",time()).'-'.$resid;
        $up['bill_id'] = $addid;
        $w['yw_id'] = $bill['yw_id'];
        $result = $this->where($w)->update($up);
        return $result;
    }
}
