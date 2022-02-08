define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'dict/index' + location.search,
                    add_url: 'dict/add',
                    edit_url: 'dict/edit',
                    del_url: 'dict/del',
                    multi_url: 'dict/multi',
                    import_url: 'dict/import',
                    value:'dic_v/value',
                    table: 'dict',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                columns: [
                    [
                        // {checkbox: true},
                        {field: 'id', title: __('ID')},
                        {field: 'name', title: __('Name'), operate: 'LIKE',sortable:true},
                        {field: 'remark', title: __('Remark'), operate: 'LIKE'},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1')}, formatter: Table.api.formatter.status},
                        // {field: 'weigh', title: __('Weigh'), operate: false},
                        // {field: 'adduser', title: __('Adduser')},


                        {field: 'operate', title: __('Operate'), table: table,
                            buttons: [
                                {name: 'value', text: '数据管理', title: '数据管理', icon: 'fa fa-database', classname: 'btn btn-xs btn-info btn-dialog   ', url: 'dict_v/value'}
                            ]
                        ,events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        value: function () {
            Controller.api.bindevent();//这个地方如果不加，会造成你有跳转页面  不会出现延迟的那种消息通知
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