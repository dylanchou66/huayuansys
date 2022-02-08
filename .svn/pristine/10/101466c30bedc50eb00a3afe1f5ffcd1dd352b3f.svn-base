define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'collection_info/index' + location.search,
                    add_url: 'collection_info/add',
                    edit_url: 'collection_info/edit',
                    del_url: 'collection_info/del',
                    multi_url: 'collection_info/multi',
                    import_url: 'collection_info/import',
                    table: 'collection_info',
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
                        // {field: 'task_id', title: __('Task_id')},
                        {field: 'collection_one_id', title: __('申请单ID'), operate: 'LIKE'},
                        {field: 'admin.nickname', title: __('Adduser')},

                        // {field: 'ids', title: __('Ids'), operate: 'LIKE'},
                        // {field: 'checkuser', title: __('Checkuser')},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'checktime', title: __('Checktime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime ,visible: false},
                        {field: 'type', title: __('结算对象'), searchList: {"1":__('分公司'),"2":__('客户')}, formatter: Table.api.formatter.normal},

                        {field: 'id', title: __('定责'),formatter: function(value,row) {

                                if ( row['type'] == 2) {
                                    var html = "";
                                    html += row['project']['company'];
                                    return html;
                                }else{
                                    var html = "";
                                    html += row['fgs']['name'];
                                    return html;
                                }
                            },operate: false
                        },

                        {field: 'money', title: __('Money'), operate: 'LIKE'},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2')}, formatter: Table.api.formatter.status},
                        {field: 'operate', title: __('Operate'), table: table,buttons: [
                                {name: 'check', text: '查看进度', title: '查看进度', icon: 'fa fa-paint-brush', classname: 'btn btn-xs btn-info btn-dialog   ',extend:'data-area=\'["600px","650px"]\'', url: 'collection_info/one'}
                            ], events: Table.api.events.operate, formatter: Table.api.formatter.operate},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});