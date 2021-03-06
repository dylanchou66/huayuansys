<?php

namespace app\admin\model;

use think\Model;


class CarPay extends Model
{

    

    

    // 表名
    protected $name = 'car_pay';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'newtime_text',
        'invoice_text',
        'status_text'
    ];
    

    
    public function getInvoiceList()
    {
        return ['0' => __('Invoice 0'), '1' => __('Invoice 1'), '2' => __('Invoice 2'), '3' => __('Invoice 3')];
    }

    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1'), '2' => __('Status 2') , '3' => __('Status 3') , '4' => __('Status 4')];

    }


    public function getNewtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['newtime']) ? $data['newtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getInvoiceTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['invoice']) ? $data['invoice'] : '');
        $list = $this->getInvoiceList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setNewtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function car()
    {
        return $this->belongsTo('Car', 'car_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function dictvalues()
    {
        return $this->belongsTo('DictValues', 'pay_type_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function addUser()
    {
        return $this->belongsTo('admin', 'adduser', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function free()
    {
        return $this->belongsTo('DictValues', 'free_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function siji()
    {
        return $this->belongsTo('DictValues', 'siji_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function jiaqizhan()
    {
        return $this->belongsTo('DictValues', 'jiaqizhan_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }





}

