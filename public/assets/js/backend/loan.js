define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'loan/index' + location.search,
                    add_url: 'loan/add',
                    re_url: 'loan/re',
                    edit_url: 'loan/edit',
                    del_url: 'loan/del',
                    multi_url: 'loan/multi',
                    import_url: 'loan/import',
                    table: 'borrow',
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
                        {field: 'borrow_id', title: __('Borrow_id'), operate: 'LIKE'},
                        {field: 'borrow_type', title: __('申请类型'), searchList: {"0":__('Type 0'),"1":__('Type 1')},custom:{"1":"Blue","0":"yellow"} ,formatter: Table.api.formatter.normal},

                        // {field: 'project_id', title: __('Project_id')},
                        {field: 'ptype.name', title: __('业务类型')},


                        {field: 'project.project', title: __('Project.project'), operate: 'LIKE'},

                        // {field: 'matter_id', title: __('Matter_id')},
                        {field: 'dictvalues.name', title: __('事项类型'), operate: 'LIKE'},

                        {field: 'money', title: __('Money_type 0'), formatter: function(value,row) {
                                if ( row['money_type'] == '0') {
                                    var html = "";
                                    html += +row['money']+'</a>';
                                    return html;
                                } else{
                                    var html = "";
                                    return html;
                                }
                            }
                        },

                        {field: 'money', title: __('Money_type 1'), formatter: function(value,row) {
                                if ( row['money_type'] == '1') {
                                    var html = "";
                                    html += +row['money']+'</a>';
                                    return html;
                                } else{
                                    var html = "";
                                    return html;
                                }
                            }},



                        {field: 'money_type', title: __('Money_type'), searchList: {"0":__('Money_type 0'),"1":__('Money_type 1')},custom:{"1":"red","0":"green"} ,formatter: Table.api.formatter.normal},
                        // {field: 'adduser', title: __('Adduser')},
                        {field: 'admin.nickname', title: __('申请人'), operate: 'LIKE'},

                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'photo_images', title: __('Photo_images'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.images},
                        // {field: 'orders_id', title: __('Orders_id')},
                        // {field: 'task_id', title: __('Task_id')},
                        {field: 'pay_time', title: __('支付时间'), operate:'RANGE', addclass:'datetimerange',visible:false, autocomplete:false,datetimeFormat:'YYYY-MM-DD', formatter: Table.api.formatter.datetime},

                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2'),"3":__('Status 3')}, formatter: Table.api.formatter.status},
                        // {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'admin.id', title: __('Admin.id')},
                        // {field: 'admin.email', title: __('Admin.email'), operate: 'LIKE'},
                        // {field: 'project.id', title: __('Project.id')},
                        // {field: 'project.company', title: __('Project.company'), operate: 'LIKE'},
                        // {field: 'dictvalues.id', title: __('Dictvalues.id')},
                        // {field: 'dictvalues.status', title: __('Dictvalues.status'), formatter: Table.api.formatter.status},
                        {field: 'operate', title: __('Operate'), table: table,buttons: [
                                {name: 'check', text: '查看进度', title: '查看进度', icon: 'fa fa-paint-brush', classname: 'btn btn-xs btn-info btn-dialog   ',extend:'data-area=\'["600px","650px"]\'', url: 'loan/one'}
                            ], events: Table.api.events.operate, formatter: Table.api.formatter.operate},
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
            table.on('load-success.bs.table', function (e, data) {
                $("#jz_ttl").text(data.jz.jz_ttl);
                $("#jz_dk").text(data.jz.jz_dk);
                $("#jz_qk").text(data.jz.jz_qk);
                $("#byj").text(data.byj.byj);

            });

        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        re: function () {
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