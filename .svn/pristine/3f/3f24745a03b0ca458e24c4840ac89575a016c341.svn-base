<?php

namespace app\admin\model;

use think\Model;


class Pay extends Model
{

    

    

    // 表名
    protected $name = 'pay';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_id_text',
        'newtime_text',
        'check_id_text',
        'status_text'
    ];
    

    
    public function getTypeIdList()
    {
        return ['0' => __('Type_id 0'), '1' => __('Type_id 1')];
    }

    public function getCheckIdList()
    {
        return ['0' => __('Check_id 0'), '1' => __('Check_id 1')];
    }

    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1'), '2' => __('Status 2') , '3' => __('Status 3') , '4' => __('Status 4'),'5' => __('Status 5')];

    }


    public function getTypeIdTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type_id']) ? $data['type_id'] : '');
        $list = $this->getTypeIdList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getNewtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['newtime']) ? $data['newtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getCheckIdTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['check_id']) ? $data['check_id'] : '');
        $list = $this->getCheckIdList();
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


    public function dictvalues()
    {
        return $this->belongsTo('DictValues', 'project_type_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function project()
    {
        return $this->belongsTo('Project', 'project_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function peytype()
    {
        return $this->belongsTo('DictValues', 'pay_type_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function adduser()
    {
        return $this->belongsTo('Admin', 'adduser', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function fgs()
    {
        return $this->belongsTo('DictValues', 'df_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
