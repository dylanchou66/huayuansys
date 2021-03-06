<?php

namespace app\admin\model;

use think\Model;


class Borrow extends Model
{

    

    

    // 表名
    protected $name = 'borrow';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'money_type_text',
        'addtime_text',
        'status_text'
    ];
    

    
    public function getMoneyTypeList()
    {
        return ['0' => __('Money_type 0'), '1' => __('Money_type 1')];
    }

    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1'), '2' => __('Status 2'), '3' => __('Status 3')];
    }

    public function getBorrowType()
    {
        return ['0' => __('Type 0'), '1' => __('Type 1')];
    }

    public function getMoneyTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['money_type']) ? $data['money_type'] : '');
        $list = $this->getMoneyTypeList();
        return isset($list[$value]) ? $list[$value] : '';
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

    protected function setAddtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function admin()
    {
        return $this->belongsTo('Admin', 'adduser', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function project()
    {
        return $this->belongsTo('Project', 'project_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function dictvalues()
    {
        return $this->belongsTo('DictValues', 'matter_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function ptype()
    {
        return $this->belongsTo('DictValues', 'project_type_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    //计算借支欠款
    public function jz_qk($user){
        $map['adduser'] = $user;
        $map['borrow_type'] = 0;//支
        $map['money_type'] = 0;//0代表收入
        $map['status'] = 3;//确认过的金额

        $mapdk['adduser'] = $user;
        $mapdk['borrow_type'] = 0;//支
        $mapdk['money_type'] = 1;//0代表收入
        $mapdk['status'] = 3;//确认过的金额
        //计算借支总金额
        $ttl = $this->where($map)->sum('money');
        $dk = $this->where($mapdk)->sum('money');
        $number = $ttl-$dk;
        return $number;
    }

    //核销写入表 pay
    public function add($user,$params,$resid,$r,$addid){
        $borrow['borrow_type'] = 0;
        $borrow['adduser'] = $user;
        $borrow['money'] = $params['borrow_money'];
        $borrow['project_type_id'] = $params['project_type_id'];
        $borrow['money_type'] = 1;
        $borrow['addtime'] = time();
        $borrow['matter_id'] = 219;
        $borrow['matter'] = '核销借支：'.$params['beizhu'].'关联单号:'.$addid;
        $borrow['photo_images'] = $params['photo_images'];
        $borrow['project_id'] = $params['project_id'];
        $borrow['gl_type'] = 158;
        $borrow['gl_order_id'] = $resid;
        $borrow['task_id'] = $r;
        $borrow['status'] = 0;
        $borrow['createtime'] = time();
        $this->insert($borrow);
        $resid = $this->getLastInsID();
        $addid = 'HY-SH-B-'.$borrow['borrow_type'] .'-'.date("Ymd",time()).'-'.$resid;
        $up['borrow_id'] = $addid;
        $w['id'] = $resid;
        $result = $this->where($w)->update($up);
        return $result;
    }

    //核销写入表 pay
    public function car_add($user,$params,$resid,$r,$addid){
        $borrow['borrow_type'] = 0;
        $borrow['adduser'] = $user;
        $borrow['money'] = $params['borrow_money'];
        $borrow['project_type_id'] = 128;
        $borrow['money_type'] = 1;
        $borrow['addtime'] = time();
        $borrow['matter_id'] = 219;
        $borrow['matter'] = '核销借支：'.$params['ps'].'关联单号:'.$addid;
        $borrow['photo_images'] = $params['photoimages'];
        $borrow['project_id'] = 44;
        $borrow['gl_type'] = 158;
        $borrow['gl_order_id'] = $resid;
        $borrow['task_id'] = $r;
        $borrow['status'] = 0;
        $borrow['createtime'] = time();
        $this->insert($borrow);
        $resid = $this->getLastInsID();
        $addid = 'HY-SH-B-'.$borrow['borrow_type'] .'-'.date("Ymd",time()).'-'.$resid;
        $up['borrow_id'] = $addid;
        $w['id'] = $resid;
        $result = $this->where($w)->update($up);
        return $result;
    }
}
