<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class SupplierInfo extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'supplier_info';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [

    ];
    

    







    public function supplier()
    {
        return $this->belongsTo('Supplier', 'supplier_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function dictvalues()
    {
        return $this->belongsTo('DictValues', 'free_ids', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function area()
    {
        return $this->belongsTo('Area', 'from_province_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function brea()
    {
        return $this->belongsTo('Area', 'from_city_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function crea()
    {
        return $this->belongsTo('Area', 'to_province_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function drea()
    {
        return $this->belongsTo('Area', 'to_city_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

}
