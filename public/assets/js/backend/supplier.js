define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'supplier/index' + location.search,
                    add_url: 'supplier/add',
                    addprivate_url: 'supplier/addprivate',
                    edit_url: 'supplier/edit',
                    del_url: 'supplier/del',
                    multi_url: 'supplier/multi',
                    import_url: 'supplier/import',
                    table: 'supplier',
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
                        {field: 'id', title: __('Id')},
                        {field: 'company', title: __('Company'), operate: 'LIKE'},
                        // {field: 'dict_values_id', title: __('Dict_values_id'), operate: 'LIKE'},
                        {field: 'date', title: __('Date'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'capital', title: __('Capital'), operate:'BETWEEN'},
                        // {field: 'workshop_ids', title: __('Workshop_ids'), operate: 'LIKE'},
                        {field: 'address', title: __('Address'), operate: 'LIKE'},
                        {field: 'officeaddress', title: __('Officeaddress'), operate: 'LIKE'},
                        {field: 'officephone', title: __('Officephone')},
                        {field: 'workaddress', title: __('Workaddress'), operate: 'LIKE'},
                        {field: 'workphone', title: __('Workphone')},
                        {field: 'legalperson', title: __('Legalperson'), operate: 'LIKE'},
                        {field: 'legalnumber', title: __('Legalnumber')},
                        {field: 'responsible', title: __('Responsible'), operate: 'LIKE'},
                        {field: 'resphone', title: __('Resphone')},
                        {field: 'operations', title: __('Operations'), operate: 'LIKE'},
                        {field: 'operphone', title: __('Operphone')},
                        {field: 'customer', title: __('Customer'), operate: 'LIKE'},
                        {field: 'cusphone', title: __('Cusphone')},
                        // {field: 'dict_values_ids', title: __('Dict_values_ids'), operate: 'LIKE'},
                        // {field: 'other_ids', title: __('Other_ids'), operate: 'LIKE'},
                        {field: 'total', title: __('Total')},
                        {field: 'managerstotal', title: __('Managerstotal')},
                        {field: 'disputes', title: __('Disputes'), searchList: {"0":__('Disputes 0'),"1":__('Disputes 1'),"2":__('Disputes 2')}, formatter: Table.api.formatter.normal},
                        {field: 'note', title: __('Note'), operate: 'LIKE'},
                        {field: 'insurance', title: __('Insurance'), searchList: {"0":__('Insurance 0'),"1":__('Insurance 1')}, formatter: Table.api.formatter.normal},
                        {field: 'third', title: __('Third'), searchList: {"0":__('Third 0'),"1":__('Third 1')}, formatter: Table.api.formatter.normal},
                        {field: 'domestic', title: __('Domestic'), searchList: {"0":__('Domestic 0'),"1":__('Domestic 1')}, formatter: Table.api.formatter.normal},
                        {field: 'fore', title: __('Fore'), operate:'BETWEEN'},
                        {field: 'other', title: __('Other'), operate: 'LIKE'},
                        // {field: 'type_ids', title: __('Type_ids'), operate: 'LIKE'},
                        {field: 'day', title: __('Day')},
                        // {field: 'check_ids', title: __('Check_ids'), operate: 'LIKE'},
                        {field: 'iso', title: __('Iso'), searchList: {"0":__('Iso 0'),"1":__('Iso 1')}, formatter: Table.api.formatter.normal},
                        // {field: 'copy', title: __('Copy'), searchList: {"0":__('Copy 0'),"1":__('Copy 1')}, formatter: Table.api.formatter.normal},
                        // {field: 'checkimages', title: __('Checkimages'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.images},
                        // {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'weigh', title: __('Weigh'), operate: false},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2')}, formatter: Table.api.formatter.status},
                        // {field: 'adduser', title: __('Adduser')},
                        // {field: 'dictvalues.id', title: __('Dictvalues.id')},
                        // {field: 'dictvalues.name', title: __('Dictvalues.name'), operate: 'LIKE'},
                        // {field: 'dictvalues.status', title: __('Dictvalues.status'), formatter: Table.api.formatter.status},
                        // {field: 'dictvalues.dict_id', title: __('Dictvalues.dict_id')},
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
        addprivate: function () {
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