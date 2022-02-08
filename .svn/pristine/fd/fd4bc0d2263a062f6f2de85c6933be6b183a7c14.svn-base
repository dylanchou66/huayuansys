define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'dict_v/index' + location.search,
                    add_url: 'dict_v/add',
                    edit_url: 'dict_v/edit',
                    del_url: 'dict_v/del',
                    multi_url: 'dict_v/multi',
                    import_url: 'dict_v/import',
                    table: 'dict_values',
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
                        {field: 'id', title: __('ID'),sortable:true},
                        {field: 'dict.name', title: __('Dict.name'), operate: 'LIKE',sortable:true},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'dict.status', title: __('Dict.status'),searchList: {"0":__('Dict.Status 0'),"1":__('Dict.Status 1')}, formatter: Table.api.formatter.status},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1')}, formatter: Table.api.formatter.status},

                        // {field: 'weigh', title: __('Weigh'), operate: false},
                        // {field: 'dict_id', title: __('Dict_id')},
                        // {field: 'adduser', title: __('Adduser')},
                        // {field: 'dict.id', title: __('Dict.id')},


                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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