define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'pay/index' + location.search,
                    add_url: 'pay/add',
                    edit_url: 'pay/edit',
                    del_url: 'pay/del',
                    multi_url: 'pay/multi',
                    import_url: 'pay/import',
                    table: 'pay',
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
                        {field: 'adduser.nickname', title: __('申请人')},

                        {field: 'pay_one_id', title: __('申请单号')},
                        {field: 'cmm_id', title: __('Cmm_id'), operate: 'LIKE'},
                        {field: 'dh_id', title: __('Dh_id'), operate: 'LIKE'},
                        {field: 'dictvalues.name', title: __('Dictvalues.name'), operate: 'LIKE'},

                        {field: 'project.project', title: __('Project.project'), operate: 'LIKE'},

                        // {field: 'project_type_id', title: __('Project_type_id')},
                        // {field: 'project_id', title: __('Project_id')},
                        {field: 'peytype.name', title: __('Pay_type_id')},
                        {field: 'type_id', title: __('Type_id'), searchList: {"0":__('Type_id 0'),"1":__('Type_id 1')}, formatter: Table.api.formatter.normal},
                        {field: 'newtime', title: __('Newtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false,datetimeFormat:'YYYY-MM-DD', formatter: Table.api.formatter.datetime},
                        {field: 'createtime', title: __('提交时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false,datetimeFormat:'YYYY-MM-DD', formatter: Table.api.formatter.datetime},

                        {field: 'id', title: __('垫付对象'),formatter: function(value,row) {
                                if ( row['df_type'] == 0) {
                                    var html = "";
                                    html += '内部</a>'
                                    return html;
                                } else if(row['df_type'] == 1){
                                    var html = "";
                                    html += '内部</div>';
                                    return html;
                                }else{
                                    var html = "";
                                    html += '客户</div>';
                                    return html;
                                }
                            }

                        },
                        {field: 'id', title: __('对象名字'),formatter: function(value,row) {
                                if ( row['df_type'] == 0) {
                                    var html = "";
                                    html += row['fgs']['name'];
                                    return html;
                                } else if(row['df_type'] == 1){
                                    var html = "";
                                    html += row['fgs']['name'];
                                    return html;
                                }else{
                                    var html = "";
                                    html += row['project']['company'];
                                    return html;
                                }
                            }

                        },


                        {field: 'pay_money', title: __('Pay_money'), operate: 'LIKE'},
                        // {field: 'check_money', title: __('Check_money'),formatter: function(value,row) {
                        //         if ( row['check_id'] == 1) {
                        //             var html = "";
                        //             html += row['check_money']
                        //             return html;
                        //         } else{
                        //             var html = "";
                        //             html += '未确认';
                        //             return html;
                        //         }
                        //     }},

                        {field: 'id', title: __('确认金额'),formatter: function(value,row) {

                            switch(row['status']) {
                                case '0':
                                    var html = "";
                                    html += '<div  class="btn btn-xs btn-warning btn-dialog"  title="待审核" data-table-id="table" data-field-index=""  data-row-index="0" data-button-index="0">';
                                    html += '待审核</a>';
                                    return html;
                                    break;
                                case '1':

                                    var html = "";
                                    html += '<div  class="btn btn-xs btn-success btn-dialog"  title="完成" data-table-id="table" data-field-index=""  data-row-index="0" data-button-index="0">';
                                    html += '完成</a>';
                                    return html;


                                    break;
                                case '2':
                                    var html = "";
                                    html += '<div  class="btn btn-xs btn-danger btn-dialog"  title="完成" data-table-id="table" data-field-index=""  data-row-index="0" data-button-index="0">';
                                    html += '驳回</a>';
                                    return html;
                                    break;
                                case '5':
                                    if (row['check_id'] == '0'){
                                            var html = "";
                                        html += '<a href="/uBPmpTIrcF.php/pay/check/ids/' + row['id'] + '" class="btn btn-xs btn-danger btn-dialog"  title="确认金额" data-table-id="table" data-field-index=""  data-row-index="0" data-button-index="0">';
                                        html += '确认金额</a>';
                                        return html;
                                    }else{
                                        var html = "";
                                        html += '<div  class="btn btn-xs btn-success btn-dialog"  title="完成" data-table-id="table" data-field-index=""  data-row-index="0" data-button-index="0">';
                                        html += '完成</a>';
                                        return html;
                                    }

                                    break;
                                default:
                                    return 321;
                            }
                                //     if (row['check_id'] == 0 && row['status'] == 1) {
                                //         var html = "";
                                //         html += '<a href="/uBPmpTIrcF.php/pay/check/ids/' + row['id'] + '" class="btn btn-xs btn-danger btn-dialog"  title="确认金额" data-table-id="table" data-field-index=""  data-row-index="0" data-button-index="0">';
                                //         html += '确认金额</a>';
                                //         return html;
                                //     }else if(row['ststus'] == 5 && row['check_id'] == 1){
                                //         var html = "";
                                //         html += '<div  class="btn btn-xs btn-warning btn-dialog"  title="完成" data-table-id="table" data-field-index=""  data-row-index="0" data-button-index="0">';
                                //         html += '完成</a>';
                                //         return html;
                                //     }else if(row['ststus'] == 2){
                                //         var html = "";
                                //         html += '<div class="btn btn-xs btn-success"  title="驳回" data-table-id="table" data-field-index=""  data-row-index="0" data-button-index="0">';
                                //         html += '驳回</div>';
                                //         return html;
                                //     }else{
                                //         var html = "";
                                //         html += '<div class="btn btn-xs btn-success"  title="完成" data-table-id="table" data-field-index=""  data-row-index="0" data-button-index="0">';
                                //         html += '完成</div>';
                                //         return html;
                                //     }
                                // }
                            }
                        },
                        // {field: 'check_id', title: __('Check_id'), searchList: {"0":__('Check_id 0'),"1":__('Check_id 1')}, formatter: Table.api.formatter.normal},
                        // {field: 'user', title: __('User'), operate: 'LIKE'},
                        // {field: 'nember', title: __('Nember'), operate: 'LIKE'},
                        // {field: 'invoice_id', title: __('Invoice_id')},
                        // {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'collection', title: __('结算状态'), custom: {"0": 'danger',"1":'dange', "2": 'success'}, searchList: {"0":__('Collction 0'),"1":__('Collction 1'),"2":__('Collction 2')}, formatter: Table.api.formatter.status},
                        {field: 'status', title: __('支付状态'), searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2'),"3":__('Status 3'),"4":__('Status 4'),"5":__('Status 5')}, formatter: Table.api.formatter.status},
                        {field: 'pay_time', title: __('支付时间'), operate:'RANGE', addclass:'datetimerange',visible:false, autocomplete:false,datetimeFormat:'YYYY-MM-DD', formatter: Table.api.formatter.datetime},


                        // {field: 'task_id', title: __('Task_id')},

                        // {field: 'dictvalues.id', title: __('Dictvalues.id')},
                        // {field: 'dictvalues.status', title: __('Dictvalues.status'), formatter: Table.api.formatter.status},
                        // {field: 'project.id', title: __('Project.id')},
                        // {field: 'project.type_id', title: __('Project.type_id')},
                        // {field: 'project.company', title: __('Project.company'), operate: 'LIKE'},
                        // {field: 'project.goods_type_ids', title: __('Project.goods_type_ids'), operate: 'LIKE'},
                        // {field: 'project.goods_name_ids', title: __('Project.goods_name_ids'), operate: 'LIKE'},
                        // {field: 'project.starttime', title: __('Project.starttime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'project.endtime', title: __('Project.endtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'project.payday', title: __('Project.payday')},
                        // {field: 'project.free_ids', title: __('Project.free_ids'), operate: 'LIKE'},
                        // {field: 'project.kefu_ids', title: __('Project.kefu_ids'), operate: 'LIKE'},
                        // {field: 'project.adduser', title: __('Project.adduser')},
                        // {field: 'project.company_id', title: __('Project.company_id')},
                        // {field: 'project.createtime', title: __('Project.createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'project.updatetime', title: __('Project.updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'project.status_switch', title: __('Project.status_switch'), table: table, formatter: Table.api.formatter.toggle},
                        // {field: 'project.weigh', title: __('Project.weigh')},
                        {field: 'beizhu', title: __('备注'), operate: 'LIKE',visible:false},

                        {field: 'operate', title: __('Operate'), table: table,buttons: [
                                {name: 'check', text: '查看进度', title: '查看进度', icon: 'fa fa-paint-brush', classname: 'btn btn-xs btn-info btn-dialog   ',extend:'data-area=\'["600px","650px"]\'', url: 'pay/one'}
                            ], events: Table.api.events.operate, formatter: Table.api.formatter.operate},
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
        detail: function () {
            Controller.api.bindevent();
        },
        check: function () {
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