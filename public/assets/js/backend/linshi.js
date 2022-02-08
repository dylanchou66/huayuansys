define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'linshi/index' + location.search,
                    add_url: 'linshi/add',
                    edit_url: 'linshi/edit',
                    del_url: 'linshi/del',
                    multi_url: 'calinshir/multi',
                    import_url: 'linshi/import',
                    table: 'car',
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
                        {field: 'id', title: __('Id'),sortable:true},
                        {field: 'hyid', title: __('Hyid'), operate: 'LIKE',sortable:true},
                        // {field: 'dictvalues.id', title: __('Dictvalues.id')},
                        {field: 'dictvalues.name', title: __('Dictvalues.name'), operate: 'LIKE',sortable:true},

                        {field: 'car', title: __('Car'), operate: 'LIKE'},
                        {field: 'carid.name', title: __('Car_id')},

                        // {field: 'number', title: __('Number'), operate: 'LIKE'},
                        // {field: 'car_type_id', title: __('Car_type_id')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        // {field: 'starttime', title: __('Starttime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'checktime', title: __('Checktime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'overtime', title: __('Overtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'check', title: __('Check')},
                        // {field: 'newcarmoney', title: __('Newcarmoney')},
                        // {field: 'oldcarmoney', title: __('Oldcarmoney')},
                        // {field: 'other_car', title: __('Other_car'), searchList: {"0":__('Other_car 0'),"1":__('Other_car 1')}, formatter: Table.api.formatter.normal},
                        // {field: 'danges_card_time', title: __('Danges_card_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'cylinder_time', title: __('Cylinder_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'long', title: __('Long')},
                        // {field: 'width', title: __('Width')},
                        // {field: 'high', title: __('High')},
                        // {field: 'cubics', title: __('Cubics')},
                        // {field: 'load', title: __('Load')},
                        // {field: 'Insurance_ids', title: __('Insurance_ids')},
                        // {field: 'insurance_type_ids', title: __('Insurance_type_ids'), operate: 'LIKE'},
                        // {field: 'money', title: __('Money')},
                        // {field: 'insurance_time', title: __('Insurance_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'car_images', title: __('Car_images'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.images},
                        // {field: 'company_id', title: __('Company_id')},
                        {field: 'adduser.nickname', title: __('Adduser')},
                        // {field: 'weigh', title: __('Weigh'), operate: false},
                        // {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1')}, formatter: Table.api.formatter.status},
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