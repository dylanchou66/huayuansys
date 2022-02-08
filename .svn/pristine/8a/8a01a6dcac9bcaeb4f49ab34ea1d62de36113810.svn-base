<?php

namespace app\admin\model;

use think\Model;


class Supplier extends Model
{

    

    

    // 表名
    protected $name = 'supplier';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'disputes_text',
        'insurance_text',
        'third_text',
        'domestic_text',
        'iso_text',
        'copy_text',
        'status_text'
    ];
    

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    
    public function getDisputesList()
    {
        return ['0' => __('Disputes 0'), '1' => __('Disputes 1'), '2' => __('Disputes 2')];
    }

    public function getInsuranceList()
    {
        return ['0' => __('Insurance 0'), '1' => __('Insurance 1')];
    }

    public function getThirdList()
    {
        return ['0' => __('Third 0'), '1' => __('Third 1')];
    }

    public function getDomesticList()
    {
        return ['0' => __('Domestic 0'), '1' => __('Domestic 1')];
    }

    public function getIsoList()
    {
        return ['0' => __('Iso 0'), '1' => __('Iso 1')];
    }

    public function getCopyList()
    {
        return ['0' => __('Copy 0'), '1' => __('Copy 1')];
    }

    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1'), '2' => __('Status 2')];
    }


    public function getDisputesTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['disputes']) ? $data['disputes'] : '');
        $list = $this->getDisputesList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getInsuranceTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['insurance']) ? $data['insurance'] : '');
        $list = $this->getInsuranceList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getThirdTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['third']) ? $data['third'] : '');
        $list = $this->getThirdList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getDomesticTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['domestic']) ? $data['domestic'] : '');
        $list = $this->getDomesticList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsoTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['iso']) ? $data['iso'] : '');
        $list = $this->getIsoList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getCopyTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['copy']) ? $data['copy'] : '');
        $list = $this->getCopyList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }




    public function dictvalues()
    {
        return $this->belongsTo('DictValues', 'dict_values_id', 'dict_id', [], 'LEFT')->setEagerlyType(0);
    }
}
