<?php

namespace app\admin\model;

use think\Model;


class Out extends Model
{

    

    

    // 表名
    protected $name = 'out';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text'
    ];
    

    
    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1'), '2' => __('Status 2')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }




    public function dictvalues()
    {
        return $this->belongsTo('DictValues', 'shenqin_type_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function area()
    {
        return $this->belongsTo('Area', 'to_city_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function chufa()
    {
        return $this->belongsTo('Area', 'from_city_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function car()
    {
        return $this->belongsTo('DictValues', 'gongju_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function adduser()
    {
        return $this->belongsTo('admin', 'adduser', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
