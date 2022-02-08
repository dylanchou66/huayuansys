define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'project/index' + location.search,
                    add_url: 'project/add',
                    edit_url: 'project/edit',
                    del_url: 'project/del',
                    multi_url: 'project/multi',
                    import_url: 'project/import',
                    table: 'project',
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
                        {checkbox: true},
                        // {field: 'id', title: __('Id')},
                        // {field: 'type_id', title: __('Type_id')},
                        {field: 'dictvalues.name', title: __('Dictvalues.name'), operate: 'LIKE'},
                        {field: 'admin.nickname', title: __('Admin.nickname'), operate: 'LIKE'},

                        {field: 'company', title: __('Company'), operate: 'LIKE'},
                        {field: 'project', title: __('Project'), operate: 'LIKE'},
                        // {field: 'goods_type_ids', title: __('Goods_type_ids'), operate: 'LIKE'},
                        // {field: 'goods_name_ids', title: __('Goods_name_ids'), operate: 'LIKE'},

                        {field: 'starttime', title: __('Starttime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'endtime', title: __('Endtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'payday', title: __('Payday')},
                        // {field: 'free_ids', title: __('Free_ids'), operate: 'LIKE'},
                        {field: 'free.name', title: __('Free.name'), operate: 'LIKE'},
                        // {field: 'kefu_ids', title: __('Kefu_ids'), operate: 'LIKE'},
                        // {field: 'adduser', title: __('Adduser')},
                        // {field: 'company_id', title: __('Company_id')},
                        // {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'status_switch', title: __('Status_switch'), searchList: {"1":__('Yes'),"0":__('No')}, table: table, formatter: Table.api.formatter.toggle},
                        // {field: 'weigh', title: __('Weigh'), operate: false},
                        // {field: 'admin.nickname', title: __('Admin.nickname'), operate: 'LIKE'},
                        // {field: 'dictvalues.name', title: __('Dictvalues.name'), operate: 'LIKE'},
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