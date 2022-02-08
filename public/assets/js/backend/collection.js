define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init();
            this.table.first();
            this.table.second();
            this.table.third();
        },
        table: {
            first: function () {
                // 表格1
                var table1 = $("#table1");
                table1.bootstrapTable({
                    url: 'collection/table1',
                    toolbar: '#toolbar1',
                    sortName: 'id',
                    search: false,
                    onClickRow:function (row,$element) {
                        $('.info').removeClass('info');
                        $($element).addClass('info');
                    },
                    cardView :true,
                    columns: [
                        [
                            // {field: 'id', title: 'ID',visible:false,},
                            {field: 'name', title: '类型:',operate: false},
                            {field: 'number', title: '未收款:',operate: false},
                            {field: 'money', title: '金额:',operate: false},
                            {
                                field: 'operate', title: __('Operate'), table: table1, events: Table.api.events.operate, buttons: [
                                    {
                                        name: 'check',
                                        title: '查看',
                                        text: '查看',
                                        icon: 'fa fa-list',
                                        classname: 'btn btn-primary btn-xs btn-click',
                                        click: function (e, data) {
                                            $("#myTabContent2 .form-commonsearch input[name='id']").val(data.id);
                                            $("#myTabContent2 .btn-refresh").trigger("click");
                                        }
                                    }
                                ], formatter: Table.api.formatter.operate
                            }
                        ]
                    ]
                });

                // 为表格1绑定事件
                Table.api.bindevent(table1);
            },
            second: function () {
                // 表格2
                var table2 = $("#table2");
                table2.bootstrapTable({
                    url: 'collection/table2',
                    extend: {
                        index_url: '',
                        add_url: '',
                        edit_url: '',
                        del_url: '',
                        multi_url: '',
                        table: '',
                    },
                    toolbar: '#toolbar2',
                    sortName: 'id',
                    search: false,
                    onClickRow:function (row,$element) {
                        $('.info').removeClass('info');
                        $($element).addClass('info');
                    },
                    cardView :true,
                    columns: [
                        [
                            {field: 'id', title: 'ID',visible:false,},
                            {field: 'cys_name', title: '名称:'},
                            {field: 'ttl', title: '未结算:',operate: false},
                            {field: 'money', title: '金额:',operate: false},
                            {
                                field: 'operate', title: __('Operate'), table: table2, events: Table.api.events.operate, buttons: [
                                    {
                                        name: 'check',
                                        title: '查看',
                                        text: '查看',
                                        icon: 'fa fa-list',
                                        classname: 'btn btn-primary btn-xs btn-click',
                                        click: function (e, data) {
                                            $("#myTabContent3 .form-commonsearch input[name='type']").val(data.type);
                                            $("#myTabContent3 .form-commonsearch input[name='type_id']").val(data.type_id);
                                            $("#myTabContent3 .btn-refresh").trigger("click");
                                        }
                                    }
                                ], formatter: Table.api.formatter.operate
                            }
                        ]
                    ]
                });

                // 为表格3绑定事件
                Table.api.bindevent(table2);
            },
            third: function () {
                // 表格3
                var table3 = $("#table3");
                table3.bootstrapTable({
                    url: 'collection/table3',
                    extend: {
                        index_url: '',
                        add_url: '',
                        edit_url: '',
                        del_url: 'collection/collection',
                        multi_url: '',
                        detail_url: 'deduction/detail',
                        table: '',
                    },
                    toolbar: '#toolbar3',
                    sortName: 'id',
                    search: false,
                    columns: [
                        [
                            //条件显示复选框
                            {checkbox: true,formatter:function(value, row, index){


                                    if (row.df_type == '1' || row.df_type == '2'){
                                        return {disabled: false};
                                    }else{
                                        return {disabled: true,checked:false};
                                    }
                                }},
                            // {field: 'id', title: 'ID'},

                            {field: 'type', title: '类型', visible: false},
                            {field: 'type_id', title: 'ID', visible: false},

                            {field: 'operate', title: __('Operate'), table: table3,buttons: [
                                    {name: 'check', text: '查看详情', title: '查看详情', icon: 'fa fa-paint-brush', classname: 'btn btn-xs btn-info btn-dialog   ',extend:'data-area=\'["600px","650px"]\'', url: 'pay/one'}
                                ], events: Table.api.events.operate, formatter: function (value, row, index) {


                    var that = $.extend({}, this);


                    var table = $(that.table).clone(true);


// $(table).data("operate-edit", null);


                    $(table).data("operate-del", null);


                    that.table = table;


                    return Table.api.formatter.operate.call(that, value, row, index);
                }
                            },

                            {field: 'pay_one_id', title: '申请单号'},
                            {field: 'newtime', title: '发生日期', operate:'RANGE', addclass:'datetimerange',formatter: Table.api.formatter.datetime, datetimeFormat:"YYYY-MM-DD"},
                            {field: 'pay_money', title: '金额', operate: false},
                            // {field: 'createtime', title: __('Createtime'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true,operate: false},
                        ]
                    ]
                });

                // 为表格3绑定事件
                Table.api.bindevent(table3);
                $(document).on("click", ".btn-selected", function () {
                    Layer.alert(JSON.stringify(table3.api.selecteddata(table)));
                });
            },
            multi: function () {
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
        }
    };
    return Controller;
});
