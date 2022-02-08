define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'bill/index' + location.search,
                    add_url: 'bill/add',
                    edit_url: 'bill/edit',
                    del_url: 'bill/del',
                    multi_url: 'bill/multi',
                    import_url: 'bill/import',
                    recharge_url: 'card/recharge',
                    distribute_url: 'card/distribute',


                    table: 'bill',
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
                        // {field: 'card_id', title: __('Card_id')},

                        {field: 'card.card_number', title: __('Card.card_number'), operate: 'LIKE'},


                        {field: 'card_bill_type', title: __('Card_bill_type'), searchList: {"0":__('Card_bill_type 0'),"1":__('Card_bill_type 1')},custom:{"1":"blue","0":"yellow"} , formatter: Table.api.formatter.normal},

                        {field: 'money', title: __('Money_type 0'), formatter: function(value,row) {
                                if ( row['money_type'] == '0') {
                                    var html = "";
                                    html += +row['money']+'</a>';
                                    return html;
                                } else{
                                    var html = "";
                                    return html;
                                }
                            },operate: false
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
                            },operate: false},
                        // {field: 'adduser', title: __('Adduser')},
                        // {field: 'money', title: __('Money'), operate:'BETWEEN'},

                        {field: 'money_type', title: __('Money_type'), searchList: {"0":__('Money_type 0'),"1":__('Money_type 1')}, custom:{"1":"red","0":"green"} ,formatter: Table.api.formatter.normal},
                        // {field: 'yw_id', title: __('Yw_id')},
                        // {field: 'yw_type', title: __('Yw_type')},
                        {field: 'ps', title: __('Ps'), operate: 'LIKE',formatter: function(value){return value.toString().substr(0, 30)}},
                        // {field: 'task_id', title: __('Task_id')},
                        // {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'addtime', title: __('Addtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},

                        // {field: 'pay_time', title: __('Pay_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'bill_id', title: __('Bill_id'), operate: 'LIKE'},

                        // {field: 'admin.id', title: __('Admin.id')},
                        // {field: 'admin.username', title: __('Admin.username'), operate: 'LIKE'},
                        // {field: 'admin.password', title: __('Admin.password'), operate: 'LIKE'},
                        // {field: 'admin.salt', title: __('Admin.salt'), operate: 'LIKE'},
                        // {field: 'admin.avatar', title: __('Admin.avatar'), operate: 'LIKE', events: Table.api.events.image, formatter: Table.api.formatter.image},
                        // {field: 'admin.email', title: __('Admin.email'), operate: 'LIKE'},
                        // {field: 'admin.loginfailure', title: __('Admin.loginfailure')},
                        // {field: 'admin.logintime', title: __('Admin.logintime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'admin.loginip', title: __('Admin.loginip'), operate: 'LIKE'},
                        // {field: 'admin.createtime', title: __('Admin.createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'admin.updatetime', title: __('Admin.updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'admin.token', title: __('Admin.token'), operate: 'LIKE'},
                        // {field: 'admin.status', title: __('Admin.status'), operate: 'LIKE', formatter: Table.api.formatter.status},
                        // {field: 'admin.dict_values_id', title: __('Admin.dict_values_id')},
                        // {field: 'card.id', title: __('Card.id')},
                        // {field: 'card.card_type', title: __('Card.card_type')},
                        // {field: 'card.card_master', title: __('Card.card_master')},
                        // {field: 'card.createtime', title: __('Card.createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'card.adduser', title: __('Card.adduser')},
                        // {field: 'card.useuser', title: __('Card.useuser')},
                        // {field: 'card.status', title: __('Card.status'), formatter: Table.api.formatter.status},
                        // {field: 'card.updatetime', title: __('Card.updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'admin.nickname', title: __('Admin.nickname'), operate: 'LIKE'},

                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2'),"3":__('Status 3')}, formatter: Table.api.formatter.status},

                        {field: 'operate', title: __('Operate'), table: table,buttons: [
                {name: 'check', text: '查看进度', title: '查看进度', icon: 'fa fa-paint-brush', classname: 'btn btn-xs btn-info btn-dialog   ',extend:'data-area=\'["600px","650px"]\'', url: 'bill/one'}
            ],  events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            table.on('load-success.bs.table', function (e, data) {
                $(data.card).each(function (i,el) {
                    $("#text_"+i+"").text(el.card_number).css("color","blue");
                    $("#"+i+"").text(":"+el.yue+"元").css("color","red");
                });
            });
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
        recharge: function () {
            Controller.api.bindevent();
        },
        distribute: function () {
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