<?php

namespace app\admin\model;

use think\Model;


class Car extends Model
{

    

    

    // 表名
    protected $name = 'car';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'starttime_text',
        'checktime_text',
        'overtime_text',
        'other_car_text',
        'danges_card_time_text',
        'cylinder_time_text',
        'insurance_time_text',
        'status_text'
    ];
    

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    
    public function getOtherCarList()
    {
        return ['0' => __('Other_car 0'), '1' => __('Other_car 1')];
    }

    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1')];
    }


    public function getStarttimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['starttime']) ? $data['starttime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getChecktimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['checktime']) ? $data['checktime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getOvertimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['overtime']) ? $data['overtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getOtherCarTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['other_car']) ? $data['other_car'] : '');
        $list = $this->getOtherCarList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getDangesCardTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['danges_card_time']) ? $data['danges_card_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getCylinderTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['cylinder_time']) ? $data['cylinder_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getInsuranceTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['insurance_time']) ? $data['insurance_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setStarttimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setChecktimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setOvertimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setDangesCardTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setCylinderTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setInsuranceTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function dictvalues()
    {
        return $this->belongsTo('DictValues', 'car_type_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function adduser()
    {
        return $this->belongsTo('admin', 'adduser', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function carid()
    {
        return $this->belongsTo('DictValues', 'car_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

}
