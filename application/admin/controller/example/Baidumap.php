<?php

namespace app\admin\controller\example;

use app\common\controller\Backend;

/**
 * 百度地图
 *
 * @icon fa fa-map
 * @remark 可以搜索百度位置，调用百度地图的相关API
 */
class Baidumap extends Backend
{
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('AdminLog');
    }

    /**
     * 查找地图
     */
    public function map()
    {
        return $this->view->fetch();
    }

    /**
     * 搜索列表
     */
    public function selectpage()
    {
        $this->model = model('Area');
        return parent::selectpage();
    }

    public function test(){
        $image = new \ZBarCodeImage("./3.jpg");
        /* Create a barcode scanner */
        $scanner = new \ZBarCodeScanner();
        /* Scan the image */
        $barcode = $scanner->scan($image);

        /* Loop through possible barcodes */
        if (!empty($barcode)) {
            foreach ($barcode as $code) {
                printf("Found type %s barcode with data %s\n", $code['type'], $code['data']);
            }
        }
    }
}
