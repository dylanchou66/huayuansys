<?php

namespace app\admin\model;

use think\Model;


class Check extends Model
{

    

    

    // 表名
    protected $name = 'check';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    







    public function dictvalues()
    {
        return $this->belongsTo('DictValues', 'check_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function admin()
    {
        return $this->belongsTo('Admin', 'check_leder_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function order()
    {
        return $this->belongsTo('Admin', 'check_order_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function boss()
    {
        return $this->belongsTo('Admin', 'check_boss_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function money()
    {
        return $this->belongsTo('Admin', 'check_money_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function pay()
    {
        return $this->belongsTo('Admin', 'check_pay_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function king()
    {
        return $this->belongsTo('Admin', 'check_king_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
