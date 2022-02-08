define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'car_pay/index' + location.search,
                    add_url: 'car_pay/add',
                    addhetong_url: 'car_pay/addhetong',
                    edit_url: 'car_pay/edit',
                    del_url: 'car_pay/del',
                    multi_url: 'car_pay/multi',
                    import_url: 'car_pay/import',
                    table: 'car_pay',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',


                columns: [
                    [
                        // {checkbox: true},
                        // {field: 'id', title: __('Id')},
                        {field: 'one_pay_id', title: __('支付单号'), operate: 'LIKE'},
                        {field: 'adduser.nickname', title: __('Adduser.nickname'), operate: 'LIKE'},
                        {field: 'car.car', title: __('Car.car'), operate: 'LIKE'},
                        {field: 'siji.name', title: __('司机'), operate: 'LIKE'},
                        {field: 'jiaqizhan.name',visible:false, title: __('加气站'), operate: 'LIKE'},
                        {field: 'jqdanjia_id',visible:false, title: __('单价'), operate: false},
                        {field: 'jqshuliang_id',visible:false, title: __('数量'), operate: false},
                        {field: 'dictvalues.name', title: __('Dictvalues.name'), operate: 'LIKE'},

                        // {field: 'pay_type_id', title: __('Pay_type_id')},
                        // {field: 'car_id', title: __('Car_id')},
                        {field: 'money', title: __('Money'), operate: 'LIKE'},
                        {field: 'newtime', title: __('Newtime'), operate:'RANGE', addclass:'datetimerange',datetimeFormat:"YYYY-MM-DD", autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'createtime', title: __('提交时间'), operate:'RANGE', addclass:'datetimerange',datetimeFormat:"YYYY-MM-DD", autocomplete:false, formatter: Table.api.formatter.datetime},

                        // {field: 'payinfo_id', title: __('Payinfo_id')},
                        {field: 'user', title: __('User'), visible:false,operate: false},
                        // {field: 'nember', title: __('Nember'), operate: 'LIKE'},
                        // {field: 'bank', title: __('Bank'), operate: 'false'},
                        // {field: 'invoice', title: __('Invoice'), searchList: {"0":__('Invoice 0'),"1":__('Invoice 1'),"2":__('Invoice 2'),"3":__('Invoice 3')}, formatter: Table.api.formatter.normal},
                        // {field: 'free.name', title: __('Free.name')},
                        // {field: 'photoimages', title: __('Photoimages'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.images},
                        // {field: 'adduser', title: __('Adduser')},
                        // {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'pay_time', title: __('支付时间'), operate:'RANGE', addclass:'datetimerange',visible:false, autocomplete:false,datetimeFormat:'YYYY-MM-DD', formatter: Table.api.formatter.datetime},

                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2'),"3":__('Status 3'),"4":__('Status 4')}, formatter: Table.api.formatter.status},
                        {field: 'cmm_status', title: __('运满满已录'), searchList: {"0":__('Cmm_status 0'),"1":__('Cmm_status 1')}, formatter: Table.api.formatter.status,visible:false},
                        {field: 'cmm_id', title: __('运满满单号'), operate: 'LIKE',visible:false},
                        {field: 'ps', title: __('备注'), operate: 'LIKE',visible:false},

                        // {field: 'car.id', title: __('Car.id')},
                        // {field: 'dictvalues.id', title: __('Dictvalues.id')},
                        {field: 'operate', title: __('Operate'), table: table,buttons: [
                                {name: 'check', text: '审核进度', title: '审核进度', icon: 'fa fa-paint-brush', classname: 'btn btn-xs btn-info btn-dialog   ',extend:'data-area=\'["600px","650px"]\'', url: 'car_pay/one'}
                            ], events: Table.api.events.operate, formatter: Table.api.formatter.operate},

                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        detail: function () {
            Controller.api.bindevent();
        },
        addhetong: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});