<?php

namespace app\admin\model;

use think\Model;


class DeductionInfo extends Model
{

    

    

    // 表名
    protected $name = 'deduction_info';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'addtime_text',
        'status_text',
        'checktime_text'
    ];
    

    
    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1'), '2' => __('Status 2')];
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


    public function getChecktimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['checktime']) ? $data['checktime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setAddtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setChecktimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function admin()
    {
        return $this->belongsTo('Admin', 'adduser', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function dictvalues()
    {
        return $this->belongsTo('DictValues', 'project_type_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function zerena()
    {
        return $this->belongsTo('Supplier', 'zeren_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function fgs()
    {
        return $this->belongsTo('DictValues', 'fgs_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

}
