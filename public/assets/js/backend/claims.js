define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'claims/index' + location.search,
                    add_url: 'claims/add',
                    edit_url: 'claims/edit',
                    del_url: 'claims/del',
                    dingze_url: 'claims/dingze',
                    multi_url: 'claims/multi',
                    import_url: 'claims/import',
                    table: 'claims',
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
                        {field: 'admin.nickname', title: __('Admin.nickname'), operate: 'LIKE'},

                        {field: 'claims_id', visible:false,title: __('Claims_id'), operate: 'LIKE'},
                        {field: 'dingdan_id',visible:false, title: __('Order_id'), operate: 'LIKE'},
                        {field: 'project.project', title: __('Project.project'), operate: 'LIKE'},
                        {field: 'dictvalues.name', title: __('Dictvalues.name'), operate: 'LIKE'},
                        {field: 'zeren', title: __('责任划分'), searchList: {"0":__('zerenm 0'),"1":__('zerenm 1'),"2":__('zerenm 2'),"3":__('zerenm 3'),"4":__('zerenm 4'),"5":__('zerenm 5')}, formatter: Table.api.formatter.normal},
                        {field: 'id', title: __('状态'),formatter: function(value,row) {
                            if ( row['zeren'] == 3 && row['status'] == 5) {
                                var html = "";
                                html += '<a href="/uBPmpTIrcF.php/claims/dingze/ids/' + row['id'] + '" class="btn btn-xs btn-danger btn-dialog"  title="定责" data-table-id="table" data-field-index=""  data-row-index="0" data-button-index="0">';
                                html += '定责</a>'
                                return html;
                            } else if(row['zeren'] == 3) {
                                var html = "";
                                html += '<div class="btn btn-xs btn-warning "  title="审批中" data-table-id="table" data-field-index=""  data-row-index="0" data-button-index="0">';
                                html += '审批中</div>';
                                return html;
                            }else{
                                    var html = "";
                                    html += '<div class="btn btn-xs btn-success "  title="已定责" data-table-id="table" data-field-index=""  data-row-index="0" data-button-index="0">';
                                    html += '已定责</div>';
                                    return html;
                                }
                            },operate:false

                        },

                        // {field: 'project_id', title: __('Project_id')},
                        // {field: 'project_type_id', title: __('Project_type_id')},
                        // {field: 'zerena.company', title: __('Responsibility_id'),operate: 'LIKE'},
                        {field: 'id', title: __('责任方'),formatter: function(value,row) {



                                if ( row['zeren'] == 0) {
                                    var html = "";
                                    html += row['zerena']['company']
                                    return html;
                                } else if(row['zeren'] == 1) {
                                    var html = "";
                                    html += row['fgs']['name'];
                                    return html;
                                }else if(row['zeren'] == 2 ){
                                    var html = "";
                                    html += '上海分公司';
                                    return html;
                                }else if(row['zeren'] == 4){
                                    var html = "";
                                    html += row['project']['company'];
                                    return html;
                                }else{
                                    var html = "";
                                    html += '未定责';
                                    return html;
                                }
                            }

                        },
                        {field: 'pay.name', title: __('Pay_type_id')},
                        {field: 'lipei.name', title: __('Claim_type_id')},
                        {field: 'date', title: __('Date'), operate:'RANGE', addclass:'datetimerange',formatter: Table.api.formatter.datetime, datetimeFormat:"YYYY-MM-DD"},
                        {field: 'createtime', title: __('提交时间'), visible:false,operate:'RANGE', addclass:'datetimerange',formatter: Table.api.formatter.datetime, datetimeFormat:"YYYY-MM-DD"},
                        {field: 'dz_time', title: __('定责时间'), visible:false,operate:'RANGE', addclass:'datetimerange',formatter: Table.api.formatter.datetime, datetimeFormat:"YYYY-MM-DD"},

                        {field: 'money', title: __('Money'), operate: 'LIKE'},
                        {field: 'insurance', title: __('Insurance'), searchList: {"0":__('Insurance 0'),"1":__('Insurance 1')}, formatter: Table.api.formatter.normal},


                        // {field: 'money', title: __('Money'), operate: 'LIKE'},




                        // {field: 'payee', title: __('Payee'), operate: 'LIKE'},
                        // {field: 'number', title: __('Number'), operate: 'LIKE'},
                        // {field: 'invoice_id', title: __('Invoice_id')},

                        // {field: 'adduser', title: __('Adduser')},
                        // {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'pay_time', title: __('支付时间'), operate:'RANGE', addclass:'datetimerange',visible:false, autocomplete:false,datetimeFormat:'YYYY-MM-DD', formatter: Table.api.formatter.datetime},
                        {field: 'deduction', title: __('结算状态'), custom: {"0": 'danger',"1":'dange', "2": 'success'}, searchList: {"0":__('Deduction 0'),"1":__('Deduction 1'),"2":__('Deduction 2')}, formatter: Table.api.formatter.status},

                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2'),"3":__('Status 3'),"4":__('Status 4'),"5":__('Status 5')}, formatter: Table.api.formatter.status},
                        // {field: 'admin.id', title: __('Admin.id')},
                        // {field: 'admin.status', title: __('Admin.status'), operate: 'LIKE', formatter: Table.api.formatter.status},
                        // {field: 'admin.dict_values_id', title: __('Admin.dict_values_id')},
                        // {field: 'project.id', title: __('Project.id')},
                        // {field: 'supplier.id', title: __('Supplier.id')},
                        // {field: 'supplier.company', title: __('Supplier.company'), operate: 'LIKE'},
                        // {field: 'dictvalues.id', title: __('Dictvalues.id')},
                        // {field: 'dictvalues.status', title: __('Dictvalues.status'), formatter: Table.api.formatter.status},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                        {field: 'ps', title: __('备注'), operate: 'LIKE',visible:false},

                        {field: 'operate', title: __('Operate'), table: table,buttons: [
                                {name: 'check', text: '查看进度', title: '查看进度', icon: 'fa fa-paint-brush', classname: 'btn btn-xs btn-info btn-dialog   ',extend:'data-area=\'["600px","650px"]\'', url: 'claims/one'}
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
        dingze: function () {
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