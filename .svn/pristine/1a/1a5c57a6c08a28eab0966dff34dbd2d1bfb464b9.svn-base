<?php

namespace app\admin\model;

use think\Model;


class DeductionInfo extends Model
{

    

    

    // 表名
    protected $name = 'deduction_info';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'insurance_text',
        'status_text',
        'zeren_text',
    ];
    

    
    public function getInsuranceList()
    {
        return ['0' => __('Insurance 0'), '1' => __('Insurance 1')];
    }

    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1'), '2' => __('Status 2'), '3' => __('Status 3'), '4' => __('Status 4'), '5' => __('Status 5')];
    }

    public function getzerenList()
    {
        return ['0' => __('zerenm 0'), '1' => __('zerenm 1'), '2' => __('zerenm 2'), '3' => __('zerenm 3'),'4' => __('zerenm 4'),'5' => __('zerenm 5')];
    }


    public function getInsuranceTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['insurance']) ? $data['insurance'] : '');
        $list = $this->getInsuranceList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getZerenTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['zeren']) ? $data['zeren'] : '');
        $list = $this->getzerenList();
        return isset($list[$value]) ? $list[$value] : '';
    }




    public function admin()
    {
        return $this->belongsTo('Admin', 'adduser', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function project()
    {
        return $this->belongsTo('Project', 'project_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function supplier()
    {
        return $this->belongsTo('Supplier', 'id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function dictvalues()
    {
        return $this->belongsTo('DictValues', 'project_type_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function zerena()
    {
        return $this->belongsTo('Supplier', 'responsibility_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function pay()
    {
        return $this->belongsTo('DictValues', 'pay_type_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function lipei()
    {
        return $this->belongsTo('DictValues', 'claim_type_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function fgs()
    {
        return $this->belongsTo('DictValues', 'fgs_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


}
