<?php

namespace app\admin\model;

use think\Model;


class Task extends Model
{

    

    

    // 表名
    protected $name = 'task';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'addtime_text',
        'leder_time_text',
        'order_time_text',
        'boss_time_text',
        'money_time_text',
        'pay_time_text',

    ];
    

    



    public function getAddtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['addtime']) ? $data['addtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getLederTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['leder_time']) ? $data['leder_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getOrderTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['order_time']) ? $data['order_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getBossTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['boss_time']) ? $data['boss_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getMoneyTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['money_time']) ? $data['money_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
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

    protected function setLederTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setOrderTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setBossTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setMoneyTimeAttr($value)
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


    public function carpay()
    {
        return $this->belongsTo('CarPay', 'orders_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function dictv()
    {
        return $this->belongsTo('DictValues', 'task_type_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function dict()
    {
        return $this->belongsTo('DictValues', 'pay_type_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function claims()
    {
        return $this->belongsTo('Claims', 'orders_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function paypay()
    {
        return $this->belongsTo('Pay', 'orders_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function borrow()
    {
        return $this->belongsTo('borrow', 'orders_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function deduction()
    {
        return $this->belongsTo('DeductionInfo', 'orders_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function bill()
    {
        return $this->belongsTo('Bill', 'orders_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function collection()
    {
        return $this->belongsTo('collection', 'orders_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }





}
