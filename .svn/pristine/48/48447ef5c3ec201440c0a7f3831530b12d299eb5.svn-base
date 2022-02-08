<?php

namespace app\admin\model;

use think\Model;


class ProjectInfo extends Model
{

    

    

    // 表名
    protected $name = 'project_info';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
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
        return $this->belongsTo('Area', 'from_area_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }




    public function project()
    {
        return $this->belongsTo('Project', 'project_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
