define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'party/index' + location.search,
                    add_url: 'party/add',
                    edit_url: 'party/edit',
                    del_url: 'party/del',
                    multi_url: 'party/multi',
                    import_url: 'party/import',
                    table: 'party',
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
                        {field: 'id', title: __('Id')},
                        // {field: 'dictvalues.id', title: __('Dictvalues.id')},
                        {field: 'dictvalues.name', title: __('Dictvalues.name'), operate: 'LIKE'},

                        {field: 'number', title: __('Number'), operate: 'LIKE'},
                        // {field: 'type_id', title: __('Type_id')},
                        {field: 'party_a', title: __('Party_a'), operate: 'LIKE'},
                        {field: 'party_b', title: __('Party_b'), operate: 'LIKE'},
                        {field: 'party_c', title: __('Party_c'), operate: 'LIKE'},
                        {field: 'party_time', title: __('Party_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'last_time_str', title: __('Last_time_str'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'last_time_end', title: __('Last_time_end'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'num', title: __('Num')},
                        {field: 'party_a_check', title: __('Party_a_check'), operate: 'LIKE'},
                        {field: 'party_b_check', title: __('Party_b_check'), operate: 'LIKE'},
                        {field: 'party_c_check', title: __('Party_c_check'), operate: 'LIKE'},
                        {field: 'payday', title: __('Payday')},
                        // {field: 'adduser', title: __('Adduser')},
                        // {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'weigh', title: __('Weigh'), operate: false},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2')}, formatter: Table.api.formatter.status},
                        // {field: 'dictvalues.status', title: __('Dictvalues.status'), formatter: Table.api.formatter.status},
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