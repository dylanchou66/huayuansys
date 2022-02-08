define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'project_info/index' + location.search,
                    add_url: 'project_info/add',
                    edit_url: 'project_info/edit',
                    del_url: 'project_info/del',
                    multi_url: 'project_info/multi',
                    import_url: 'project_info/import',
                    table: 'project_info',
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
                        // {field: 'id', title: __('Id')},
                        // {field: 'project_id', title: __('Project_id')},
                        {field: 'project.project', title: __('Project.project'), operate: 'LIKE'},
                        {field: 'from_name', title: __('From_name'), operate: 'LIKE'},
                        {field: 'from_phone', title: __('From_phone')},
                        {field: 'area.name', title: __('Area.name'), operate: 'LIKE'},
                        // {field: 'from_province_id', title: __('From_province_id')},
                        {field: 'brea.name', title: __('From_city_id')},
                        {field: 'crea.name', title: __('From_area_id'), operate: 'LIKE'},
                        {field: 'from_address', title: __('From_address'), operate: 'LIKE'},
                        // {field: 'to_name', title: __('To_name'), operate: 'LIKE'},
                        // {field: 'to_phone', title: __('To_phone')},
                        // {field: 'to_province_id', title: __('To_province_id')},
                        // {field: 'to_city_id', title: __('To_city_id')},
                        // {field: 'to_area_id', title: __('To_area_id')},
                        // {field: 'to_address', title: __('To_address'), operate: 'LIKE'},
                        {field: 'status_switch', title: __('Status_switch'), searchList: {"1":__('Yes'),"0":__('No')}, table: table, formatter: Table.api.formatter.toggle},
                        // {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        // {field: 'weigh', title: __('Weigh'), operate: false},
                        // {field: 'adduser', title: __('Adduser')},

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