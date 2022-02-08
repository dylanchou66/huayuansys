define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'out/index' + location.search,
                    add_url: 'out/add',
                    edit_url: 'out/edit',
                    del_url: 'out/del',
                    multi_url: 'out/multi',
                    import_url: 'out/import',
                    table: 'out',
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
                        // {field: 'shenqin_type_id', title: __('Shenqin_type_id')},
                        {field: 'out_id', title: __('Out.id'), operate: 'LIKE'},
                        {field: 'adduser.nickname', title: __('Adduser.name'), operate: 'LIKE'},
                        {field: 'dictvalues.name', title: __('Dictvalues.name'), operate: 'LIKE'},
                        {field: 'chufa.name', title: __('Mudi.name'), operate: 'LIKE'},
                        {field: 'area.name', title: __('To.name'), operate: 'LIKE'},
                        {field: 's_time', title: __('S_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'e_time', title: __('E_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'car.name', title: __('Gongju.name'), operate: 'LIKE'},


                        // {field: 'from_s_id', title: __('From_s_id')},
                        // {field: 'from_city_id', title: __('From_city_id')},
                        // {field: 'to_s_id', title: __('To_s_id')},
                        // {field: 'to_city_id', title: __('To_city_id')},
                        // {field: 'gongju_id', title: __('Gongju_id')},
                        {field: 'money', title: __('Money'), operate: false},
                        // {field: 'adduser', title: __('Adduser')},
                        // {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2')}, formatter: Table.api.formatter.status},
                        // {field: 'dictvalues.id', title: __('Dictvalues.id')},

                        // {field: 'dictvalues.status', title: __('Dictvalues.status'), formatter: Table.api.formatter.status},
                        // {field: 'area.id', title: __('Area.id')},
                        // {field: 'area.pid', title: __('Area.pid')},
                        // {field: 'area.shortname', title: __('Area.shortname'), operate: 'LIKE'},

                        // {field: 'area.mergename', title: __('Area.mergename'), operate: 'LIKE'},
                        // {field: 'area.level', title: __('Area.level')},
                        // {field: 'area.pinyin', title: __('Area.pinyin'), operate: 'LIKE'},
                        // {field: 'area.code', title: __('Area.code'), operate: 'LIKE'},
                        // {field: 'area.zip', title: __('Area.zip'), operate: 'LIKE'},
                        // {field: 'area.first', title: __('Area.first'), operate: 'LIKE'},
                        // {field: 'area.lng', title: __('Area.lng'), operate: 'LIKE'},
                        // {field: 'area.lat', title: __('Area.lat'), operate: 'LIKE'},
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