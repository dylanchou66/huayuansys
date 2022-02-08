define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'supplier_info/index' + location.search,
                    add_url: 'supplier_info/add',
                    edit_url: 'supplier_info/edit',
                    del_url: 'supplier_info/del',
                    multi_url: 'supplier_info/multi',
                    import_url: 'supplier_info/import',
                    table: 'supplier_info',
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
                        {checkbox: true},
                        {field: 'supplier.company', title: __('Supplier.company'), operate: 'LIKE'},
                        {field: 'area.name', title: __('Area.name'), operate: 'LIKE'},
                        {field: 'brea.name', title: __('Brea.name'), operate: 'LIKE'},
                        {field: 'crea.name', title: __('Crea.name'), operate: 'LIKE'},
                        {field: 'drea.name', title: __('Drea.name'), operate: 'LIKE'},
                        // {field: 'id', title: __('Id')},
                        // {field: 'supplier_id', title: __('Supplier_id')},
                        // {field: 'from_province_id', title: __('From_province_id')},
                        // {field: 'from_city_id', title: __('From_city_id')},
                        // {field: 'to_province_id', title: __('To_province_id')},
                        // {field: 'to_city_id', title: __('To_city_id')},
                        {field: 'timeall', title: __('Timeall')},
                        {field: 'int', title: __('Int'), operate:'BETWEEN'},
                        {field: 'paycar', title: __('Paycar'), operate:'BETWEEN'},
                        {field: 'lowpay', title: __('Lowpay'), operate:'BETWEEN'},
                        // {field: 'free_ids', title: __('Free_ids'), operate: 'LIKE'},
                        {field: 'pay', title: __('Pay'), operate:'BETWEEN'},
                        {field: 'pay1', title: __('Pay1'), operate:'BETWEEN'},
                        {field: 'pay2', title: __('Pay2'), operate:'BETWEEN'},
                        {field: 'pay3', title: __('Pay3'), operate:'BETWEEN'},
                        {field: 'pay4', title: __('Pay4'), operate:'BETWEEN'},
                        {field: 'pay5', title: __('Pay5'), operate:'BETWEEN'},
                        {field: 'pay6', title: __('Pay6'), operate:'BETWEEN'},
                        {field: 'pay7', title: __('Pay7'), operate:'BETWEEN'},
                        {field: 'dictvalues.name', title: __('Dictvalues.name'), operate: 'LIKE'},
                        // {field: 'adduser', title: __('Adduser')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'supplier.id', title: __('Supplier.id')},

                        // {field: 'dictvalues.id', title: __('Dictvalues.id')},

                        // {field: 'dictvalues.status', title: __('Dictvalues.status'), formatter: Table.api.formatter.status},
                        // {field: 'dictvalues.dict_id', title: __('Dictvalues.dict_id')},
                        // {field: 'area.id', title: __('Area.id')},
                        // {field: 'area.pid', title: __('Area.pid')},
                        // {field: 'area.shortname', title: __('Area.shortname'), operate: 'LIKE'},

                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        recyclebin: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    'dragsort_url': ''
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: 'supplier_info/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {
                            field: 'deletetime',
                            title: __('Deletetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            width: '130px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'Restore',
                                    text: __('Restore'),
                                    classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                    icon: 'fa fa-rotate-left',
                                    url: 'supplier_info/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'supplier_info/destroy',
                                    refresh: true
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});