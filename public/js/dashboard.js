$(function() {
    window.userId = "";
    window.userCreatedDate = "";
    window.requestId = "";
    window.minCPCBid = "";
    window.minCPMBid = "";
    window.minCPABid = "";
    window.pageSize = "";
    window.paymentInfoUpdated = "";
    window.messageShow = "";
    window.repeatMsg = "";
    window.emailMsgShow = "";
    window.advFunds = "";
    window.minDailyBudget = '';
    window.maxDailyBudget = '';
    window.continueRegistration = '';
    window.mainUserId = 0;
    window.accessRules = [];
    window.loggedInUserId = '';
    if($("#advertiser-dashboard-page-info").attr("advertiser-dashboard-page-data")) { 
        var data = JSON.parse($("#advertiser-dashboard-page-info").attr("advertiser-dashboard-page-data"));
        window.userId = data.user_id;
        window.userCreatedDate = data.user_created_date;
        window.requestId = data.request_id;
        window.minCPCBid = data.minCPCBid;
        window.minCPMBid = data.minCPMBid;
        window.minCPABid = data.minCPABid;
        window.pageSize = data.page_size;
        window.paymentInfoUpdated = data.payment_info_updated;
        window.messageShow = data.message_show;
        window.repeatMsg = data.repeat_msg;
        window.emailMsgShow = data.email_msg_show;
        window.advFunds = data.adv_funds;
        window.minDailyBudget = data.min_daily_budget;
        window.maxDailyBudget = data.max_daily_budget;
        window.continueRegistration = data.continue_registration;
        window.mainUserId = data.main_user_id;
        window.loggedInUserId = data.logged_in_user_id;
        var rules = JSON.parse(data.access_rules);
        for (ruleIndex in rules) {
            window.accessRules.push(parseInt(rules[ruleIndex]));
        }
    }
    
    //init notices
    $.initialize();
    
    $( "#min_date, #max_date" ).datepicker({
        showOn: "both",
        buttonImage: "/images/calender.png",
        buttonImageOnly: true,
        dateFormat: 'yy-mm-dd'
    }).addClass('datechooser');

    $("#chart_filter_period").selectBox('value', 'today');  
    $("#chart_filter_status").selectBox('value', '-1');  

    //load page data
    advertiserDashboardHighCharts.init();
    campaignsList.setUp();
    dashboardQuickStats.setUp();
    dashboardSummaryStats.setUp();
    exportCSVReport.setUp();
    
    $(".chkbxall").click(setCheckboxState);
    
    //Resend the email for the user for activation of account
    $("#resendLink").click(function(){ 
        //don't allow user to click again
        $("#resendLink").parents(".notification").hide();
        $.ajax({
            type: 'POST',
            url: '/index/resend-email',
            data: 'resend=1',
            success: function(response) {
                if(response) {					
                    if( response.isSend == true )
                    {
                        jAlert('Email sent successfully. Please check your email', 'Email Send Sucessfully');
                        //$("#resendLink").parent().hide();
                                                
                        if($("#resendLink").parents(".notification").is(':last-child')) {
                                                   
                            $("#resendLink").parents(".notification").remove();
                            $('#notifications .notification:last-child').removeClass("more");
                        } else {
                            $("#resendLink").parents(".notification").remove();
                        }
                                                
                    }	
                }
            }
        });
    });  
    
    // $('#campaigns_list').tablesorter();
    statusChange.init();
    campaignChange.init();
    
    $('select').not('.all_zones, .zones').selectBox();
    $('#chart_filter_period + a').addClass("selectBox2");  
    
    $('#chart_filter_status').bind('change', function(){
        var statusId = $(this).val();
        if(statusId == -1) {
            $('.campaign_item').not('.status_id_9').show();
            $('.campaign_item.status_id_9').hide();
        } else {
            $('.campaign_item.status_id_' + statusId).show();
            $('.campaign_item').not('.status_id_' + statusId).hide();
        }
        $('.campaign_item.site').addClass('odd');
    });  
    
});

var setCheckboxState = function() {
    var c = document.getElementsByName("cbox");
    var s = this.checked;
    for (var i=0; i < c.length; i++) {
        if(c[i].disabled != true)
        {
            c[i].checked = s;
        }
    }       
}

advertiserDashboardHighCharts = {
    initDashboarReportFilter:true,
    init:function() {
        //as we are nesting js objects.
        if ( typeof (this.initDashboarReportFilter) != 'undefined' && this.initDashboarReportFilter) {
            dashboarReportFilter.urlByDays = '/report/get-advertiser-daily-stats';
            dashboarReportFilter.urlByMonth = '/report/get-advertiser-monthly-stats';
            dashboarReportFilter.urlToday = '/report/get-advertiser-hourly-stats';
        
            //collect campaign ids.
            //var campaignIds = new Array();
            //$.each($('#advertiser_dashboard_campaigns_list tbody div.chbx_campaign'), function() {
            //    campaignIds.push($(this).metadata().id);
            //});
            dashboarReportFilter.postParams = {'user_id':userId};
            dashboarReportFilter.init();
            dashboarReportFilter.onFilterChange();
        }
    }
}

var pageUtil = {
    addTbodyLoader: function(tableSelector) {
        var colspan = this.countColspan(tableSelector);
        $(tableSelector).find('tbody').html('<tr><td class="loader" colspan='+colspan+'></td></tr>');
    },
    countColspan: function(tableSelector) {
        return $(tableSelector).find('thead').find('th').length;
    },
    notifyNoData: function(tableSelector) {
        var colspan = this.countColspan(tableSelector);
        $(tableSelector).find('tbody').html('<tr><td colspan='+colspan+'>Items not found.</td></tr>')
    }
}

exportCSVReport = {
    reportURL : '/advertiser/export-dashboard',
    setUp : function () {
        self = this;
        //advertiser dashboard campaigns
        $("#exp_csv_adv_dash").click(function(){
            self.loadCampaigns();
        });
    },
    
    loadCampaigns : function () {
        var params = campaignsList.getParams();
        var reportContainer = $( '<div id="export_report_container">'
            +'<form method="post" action="'+this.reportURL+'" target="exportReportFrame" nctype="multipart/form-data" style="position:absolute;visibility:hidden">'
            +'<input type="hidden" name="export_csv_criteria" id="export_csv_criteria" value="'+jQuery.param(params)+'"/></form>' 
            +'<iframe src="javascript:false" name="exportReportFrame" frameBorder="0" border="0" style="width:0;height:0"></iframe>'
            +'</div>');

        $('body').append(reportContainer);
        $('#export_report_container form').submit();  

        /*
        $.ajax({
            url: this.reportURL,
            type: 'POST',
            data: $(reportContainer).find('form').serialize(),
            dataType:'json',
            success: function(response) {
                if(response.success) {
                    
                } 
            }
        });    
        */
         
        setTimeout(function(){
           reportContainer.remove();
        }, 6000);
    }
}

var dashboardSummaryStats = {
    dataURL : "/ajax/get-advertiser-dashboard-summary",
    setUp: function() {
        var self = this;
        $("#chart_filter_status").change(function(){
            self.getData();
        });
        $("#chart_filter_period").change(function(){
            var value = $(this).val();
            if(value == 'custom') {
                $('#customDate').show();
            } else {
                $('#min_date').val("");
                $('#max_date').val("");                
                $('#customDate').hide(); 	 
            }
        })
        $("#chart_filter_period").change(function(){
            if ($(this).val() != 'custom' ) {
                self.getData();
            } 
        });
        $('.datechooser').change(function(){
            if( $('#min_date').val() != '' && 
                $('#max_date').val() != '') {
                self.getData(); 
            }
        });
        self.getData();
    },

    onChange: function(period) {
        this.getData();
    },
    
    getParams : function() {
        var statusIds = "1,3,6,10";
        if ($("#chart_filter_status").val() != -1) {
            statusIds = $("#chart_filter_status").val();
        }
        var period = $("#chart_filter_period").val();
        if (period == 'time_preriod') {
            period = 'today';
        }
        
        //we want to load summary w/o status
        //'status_id' : statusIds, 
        
        var params = {
            'user_id' : userId,
            'period':period,
            'start_date' : $("#min_date").val(),
            'end_date' : $("#max_date").val()
        };
        return params;
    },

    getData: function(period) {
        var self = this;
        var params = self.getParams();
        $.ajax({
            url: self.dataURL,
            type: 'POST',
            data: params,
            dataType:'json',
            success: function(response) {
                if(response.success) {
                    self.renderData(response.data);
                } else {
                    $('.summary div h3, .summary div h4').show();
                    $('.summary .stat_box').removeClass('loader');
                }
            }
        });
    },
    renderData: function(data) {
        $('.summary div h3, .summary div h4').show();
        $('.summary .impressions h3').text(data.impressions);
        $('.summary .clicks h3').text(data.clicks);
        $('.summary .ctr h3').text(data.ctr + '%');
        $('.summary .avg_cpc h3').text('$' + data.avg_cpc);
        $('.summary .costs h3').text('$' + data.costs);
        $('.summary .stat_box').removeClass('loader');
    }
}

dashboardQuickStats = {
    dataURL : "/ajax/get-advertiser-dashboard-quick-stats",
    setUp: function() {
        var self = this;
        $("#chart_filter_status").change(function(){
            self.getData();
        });
        $("#chart_filter_period").change(function(){
            if ($(this).val() != 'custom' ) {
                self.getData();
            } 
        });
        $('.datechooser').change(function(){
            if( $('#min_date').val() != '' && 
                $('#max_date').val() != '') {
                self.getData();
            }
        });
        self.getData();
    },
    onSelect: function() {
        this.getData();
    },
    getParams : function() {
        
        var statusIds = "1,3,6,10";
        if ($("#chart_filter_status").val() != -1) {
            statusIds = $("#chart_filter_status").val();
        }
        
        var period = $("#chart_filter_period").val();
        if (period == 'time_preriod') {
            period = 'today';
        }
        
        //we want to load summary w/o status
        //'status_id' : statusIds, 
        
        var params = {
            'user_id' : userId,
            'period':period,
            'start_date' : $("#min_date").val(),
            'end_date' : $("#max_date").val()
        };
        
        return params;
    },
    getData : function() {
        var self = this;
        var params = self.getParams();
        
        $.ajax({
            url: self.dataURL,
            type: 'POST',
            data: params,
            dataType:'json',
            success: function(response) {
                if (response.success) {
                    self.renderData(response.data);
                    self.displayDate();
                }
            }
        });
    },
    renderData: function(data) {
        $('.quick_stat').show();
        $('.quick_stat #quickstat_impressions').text(data.impressions);
        $('.quick_stat #quickstat_clicks').text(data.clicks);
        $('.quick_stat #quickstat_ecpm').text(data.ecpm);
        $('.quick_stat #quickstat_avg_cpc').text(data.avg_cpc);
        $('.quick_stat #quickstat_conversions').text(data.conversions);
        $('.quick_stat #quickstat_costs').text(data.costs);
        $('.quick_stat #quickstat_profit').text(data.profit);
        $('.quick_stat #quickstat_rpm').text(data.rpm);
        $('.quick_stat #quickstat_epc').text(data.epc);
    },
    displayDate : function() {
        
        var period = $("#chart_filter_period").val();
        if (period == 'time_preriod') {
            period = 'today';
        }
        
        var months  =   new Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
        
        var myDate=new Date();
        if ( period == "today" ) {
            $("#dashboard_quickstat_date").html(months[myDate.getMonth()]+" "+ myDate.getDate()+", "+ myDate.getFullYear());
        } else if( period =="yesterday" ) {
            myDate.setDate(myDate.getDate() - 1);
            $("#dashboard_quickstat_date").html(months[myDate.getMonth()]+" "+ myDate.getDate()+", "+ myDate.getFullYear());
        } else if (  period == "custom" ) {            
            var startDate = $('#min_date').val();
            var endDate = $('#max_date').val();
            var startDateSplit = startDate.split('-');
            var endDateSplit = endDate.split('-');
            var range = months[startDateSplit[1]-1]+" "+ startDateSplit[2]+", "+ startDateSplit[0]+" - "+months[endDateSplit[1]-1]+" "+ endDateSplit[2]+", "+ endDateSplit[0];
            $("#dashboard_quickstat_date").html(range);
            if($('#dashboard_quickstat_date').text().length > 26) {
                $("#dashboard_quickstat_date").html($("#dashboard_quickstat_date").text().substr(0,23)+'...');

                $('.hd_half h3').live('mouseenter', function() {
                    var el = $(this);
                    el.before('<div class="hd_full_title rounded_all">Quick Stats '+range+' </div>');
                }).live('mouseleave', function() {
                    $('.hd_full_title').remove();
                });
            }
        } else {
            $("#dashboard_quickstat_date").html($("#chart_filter_period option:selected").text());
        }
        
        if( period != "custom" ) {
            $('.hd_half h3').live('mouseenter', function() {                
                    $('.hd_full_title').remove();               
            });
        }
    }
}

campaignsList = {
    pageSize: 10,
    selectorTable: '#advertiser_dashboard_campaigns_list',
    dataURL : "/ajax/get-advertiser-dashboard-campaigns",    
    minCPCBid : '0.01',
    minCPMBid : '0.04',
    minCPABid : '0.20',
    minDailyBudget : '10.00',
    maxDailyBudget : '10000.00',
    userId : '',
    
    setUp: function() {
        var self = this;
        $("#chart_filter_status").change(function(){
            self.getItems();
        });
        $("#chart_filter_period").change(function(){
            if ($(this).val() != 'custom' ) {
                $("#chart_filter_size").selectBox('destroy');
                $("#chart_filter_size").hide(); 
                $("#chart_filter_size").show();  
                $("#chart_filter_size").selectBox({});
                self.getItems()
            } else {
                pageUtil.notifyNoData(self.selectorTable);    
                $('.wijmo-wijgrid-footer').remove();
                $("#chart_filter_size").selectBox('destroy');
                $("#chart_filter_size").hide(); 
            }
        });
        $('.datechooser').change(function(){
            if( $('#min_date').val() != '' && 
                $('#max_date').val() != '') {
                var startDate = $('#min_date').val().replace(/-/g, '/');
                var endDate = $('#max_date').val().replace(/-/g, '/');            
                var sDate = new Date(startDate);
                var eDate = new Date(endDate);
            
                if (sDate > eDate) {
                    jAlert("End Date should be greater than or equal to Start Date. Please re-enter.");  
                    $('#min_date').val('');
                    $('#max_date').val('');
                    pageUtil.notifyNoData(self.selectorTable);    
                    $('.wijmo-wijgrid-footer').remove();
                    $("#chart_filter_size").selectBox('destroy');
                    $("#chart_filter_size").hide();
                    return false;
                } 
                $("#chart_filter_size").selectBox('destroy');
                $("#chart_filter_size").hide(); 
                $("#chart_filter_size").show();  
                $("#chart_filter_size").selectBox({});
                self.getItems()
            }
        });
        
        // redraw grid based on page size
        $("#chart_filter_size").bind('change', function(){
            self.pageSize = $(this).val();
            self.getItems()
        });
        
        //check current size
        self.pageSize = $("#chart_filter_size").val();
        self.minCPCBid = window.minCPCBid;
        self.minCPMBid = window.minCPMBid;
        self.minCPABid = window.minCPABid;
        self.minDailyBudget = window.minDailyBudget;
        self.maxDailyBudget = window.maxDailyBudget;
        self.userId = window.userId;
        
        //load initial grid data.
        self.getItems()
    },
    
    getItems: function() {
        this.getData();
        this.bindCheckAll();
        this.onCheckBoxChanged();
    },

    getData: function() {
        
        var self = this;
        pageUtil.addTbodyLoader(self.selectorTable);
        
        var params = self.getParams();
        
        //use actual dynamic data loading.
        self.loadCampaigns(params, this.dataURL);
    },
    
    getParams : function () {
        var statusIds = "1,3,6,10";
        if ($("#chart_filter_status").val() != -1) {
            statusIds = $("#chart_filter_status").val();
        }
        
        var period = $("#chart_filter_period").val();
        if (period == 'time_preriod') {
            period = 'today';
        }
        
        var params = {
            'status_id' : statusIds, 
            'user_id' : window.userId,
            'period':period,
            'start_date' : $("#min_date").val(),
            'end_date' : $("#max_date").val()
        };
        
        return params;
    },
    
    loadCampaigns: function(params, dataUrl) {
        var self = this;
        
        // reset grid and reset headers
        $(campaignsList.selectorTable).resetWijgrid();

        //initialize grid
        $(campaignsList.selectorTable).wijgrid({
            allowColMoving: true,
            allowSorting: true,
            allowPaging: true,
            readAttributesFromData: true,
            selectionMode: 'none',
            pageSize: campaignsList.pageSize,
 
            loading: function(){
                //show loader
                $(self.selectorTable + ' tbody').html('');
                pageUtil.addTbodyLoader(self.selectorTable);
            },
            
            loaded: function (e) { 
                $('td[data-editable="campaign-cpc"]').enableCPCEdits(self.minCPCBid, self.minCPMBid, self.minCPABid);
                
                var rowIndex = 0;
                $(campaignsList.selectorTable).find('td[data-editable="campaign-cpc"]').each(function(){
                    $(this).attr('data-index', rowIndex++ );
                });
                if (rowIndex) {
                    $("#export_advertiser_dashboard").show();
                } else {
                    $("#export_advertiser_dashboard").hide();
                }
                
                $('td.edit-campaign-budget').enableDailyBudgetEdits(self.minDailyBudget, self.maxDailyBudget);
                var rowIndex = 0;
                $(campaignsList.selectorTable).find('td.edit-campaign-budget').each(function(){
                    $(this).attr('data-row_index', rowIndex++ );
                });
            },
            
            data: new wijdatasource({
                dynamic:true,
                proxy: new wijhttpproxy({
                    url: dataUrl,
                    dataType: "json",
                    key: "campaigns",
                    data:{
                        'params':params
                    }
                }),
                
                reader: {
                    read: function (dataSource) {
                        var count = parseInt(dataSource.data.__count, 10);
                        dataSource.data = dataSource.data.results;
                        dataSource.data.totalRows = count;
                                
                        new wijarrayreader([
                        {name: "id", mapping: "id"},
                        {name: "name", mapping: "name"},
                        {name: "status_name", mapping: "status_name"},
                        {name: "daily_budget", mapping: "daily_budget"},
                        {name: "impressions", mapping: "impressions"},
                        {name: "clicks", mapping: "clicks"},
                        {name: "conversions", mapping: "conversions"},
                        {name: "cpa", mapping: "cpa"},
                        {name: "ctr", mapping: "ctr"},
                        {name: "bid", mapping: "bid"},
                        {name: "costs", mapping: "costs"},
                        {name: "status_id", mapping: "status_id"},
                        {name: "campaign_type_id", mapping: "campaign_type_id"},
                        {name: "user_id", mapping: "user_id"},
                        {name: "cpc", mapping: "cpc"},
                        {name: "campaign_level_targeting", mapping: "campaign_level_targeting"},
                        {name: "created", mapping: "created"}
                        ]).read(dataSource);
                    }
                }
            }),
            
            columns: [  
            {
                headerText: "<div class=\"chkbx\"></div>",
                dataKey:'id',
                dataType: "number",
                dataFormatString: "d",
                allowSort:false,
                cellFormatter: function (args) {
                    if (args.row.type & $.wijmo.wijgrid.rowType.data) {
                        var html = '<div class="chkbx cmp_item chbx_campaign {id: ' + args.row.data.id + '}"></div>';
                        args.$container.html(html); 
                        return true;
                    }
                }
            }, 
            {
                headerText: "Campaigns", 
                dataKey:'name',
                dataType: "string", 
                filterOperator: "Contains",
                cellFormatter: function (args) {
                    if (args.row.type & $.wijmo.wijgrid.rowType.data) {
                        var name = args.formattedValue;
                        name = (name.length> 40 ? name.substr(0,38)+'&#8230;' : name);
                        var href = '<a target="_blank" href="/advertiser/campaign-edit-overview/id/'+args.row.data.id+'">'+name+'</a>';
                        args.$container.html(href);
                        args.$container.closest("td").addClass('clickable').attr('title', args.formattedValue ).css({
                            "width": "270px",
                            "overflow":"hidden"
                        });
                        return true;
                    }
                }
            },
            {
                headerText: "Status",
                dataKey:'status_name',
                dataType: 'string',
                cellFormatter: function (args) {
                    if (args.row.type & $.wijmo.wijgrid.rowType.data) {
                        var statusName = args.formattedValue;
                        var campaignId = args.row.data.id;
                        var statusId = args.row.data.status_id;
                        var html = '<div class="select_status_name">' +
                        '<div class="status_name"><span class="bg"><img src="/images/ico-'+statusName.toLowerCase()+'.png" class="status_icon_cr" />'+statusName+'</span></div>'+
                        '<div id="campaign_status_options_'+campaignId+'" class="campaign_status_options">' +
                        '<ul class="cso_items">';
                        if ( statusId == 3 ) {
                            html += '<li class="disabled"><img src="/images/icon-small-clock.png" />Scheduled</li>'; 
                        } 
                        //run li
                        if ( statusId != 6 && ( window.__k || statusId == 2 || statusId == 3 || statusId == 7) ) {
                            html += '<li id ="status_option_6_'+campaignId+'" onclick="statusChange.bindOnOptionChanged(6, '+campaignId+', \'Running\');"><img src="/images/ico-running.png" />Run</li>'; 
                        } 
                        //pause li
                        if ( statusId == 6 || statusId == 10 ) {
                            html += '<li id ="status_option_7_'+campaignId+'" onclick="statusChange.bindOnOptionChanged(7, '+campaignId+', \'Paused\');"><img src="/images/ico-paused.png" />Pause</li>'; 
                        }
                        //decline li
                        if ( statusId != 9 && statusId != 4 && window.__k ) {
                            html += '<li id ="status_option_4_'+campaignId+'" onclick="statusChange.bindOnOptionChanged(4, '+campaignId+', \'Declined\');"><img src="/images/ico-declined.png" />Declined</li>'; 
                        }
                        //delete li
                        if ( statusId != 9 ) {
                            html += '<li id ="status_option_9_'+campaignId+'" onclick="statusChange.bindOnOptionChanged(9, '+campaignId+', \'Deleted\');"><img src="/images/ico-deleted.png" />Delete</li>'; 
                        }
                        html +=     '</ul>' +
                        '</div>' +
                        '</div>';   
                        args.$container.html(html);
                        args.$container.closest("td").attr('id', campaignId ).css({
                            "width": "90px",
                            "overflow":"hidden"
                        });
                        return true;
                    }
                }
            },
            {
                headerText: "Daily Budget",
                dataKey:'daily_budget',
                dataType: "currency", 
                cellFormatter: function (args) {
                    if (args.row.type & $.wijmo.wijgrid.rowType.data) {
                        args.$container.html(args.formattedValue);
                        args.$container.closest("td")
                        .addClass('edit-campaign-budget')
                        .attr('id', "campaign_budget_"+args.row.data.id)
                        .attr('data-campaign_id', args.row.data.id )
                        .attr('data-user_id', self.userId)
                        .css({
                            "width": "100px",
                            "overflow":"hidden"
                        });
                        return true;
                    }
                }
            },
            {
                headerText: "Impressions",
                dataKey:'impressions',
                dataType: "number", 
                dataFormatString: "n:0",
                cellFormatter: function (args) {
                    if (args.row.type & $.wijmo.wijgrid.rowType.data) {
                        args.$container.html(args.formattedValue);
                        args.$container.closest("td").css({
                            "width": "100px",
                            "overflow":"hidden"
                        });
                        return true;
                    }
                }
            },
            {
                headerText: "Clicks",
                dataKey:'clicks',
                dataType: "number", 
                dataFormatString: "n:0",
                cellFormatter: function (args) {
                    if (args.row.type & $.wijmo.wijgrid.rowType.data) {
                        args.$container.html(args.formattedValue);
                        args.$container.closest("td").css({
                            "width": "70px",
                            "overflow":"hidden"
                        });
                        return true;
                    }
                }
            },
            {
                headerText: "Conv.",
                dataKey:'conversions',
                dataType: "number", 
                dataFormatString: "n:0",
                cellFormatter: function (args) {
                    if (args.row.type & $.wijmo.wijgrid.rowType.data) {
                        args.$container.html(args.formattedValue);
                        args.$container.closest("td").css({
                            "width": "60px",
                            "overflow":"hidden"
                        });
                        return true;
                    }
                }
            },
            {
                headerText: "CPA",
                dataKey:'cpa',
                dataType: "currency",
                cellFormatter: function (args) {
                    if (args.row.type & $.wijmo.wijgrid.rowType.data) {
                        args.$container.html(args.formattedValue);
                        args.$container.closest("td").css({
                            "width": "60px",
                            "overflow":"hidden"
                        });
                        return true;
                    }
                }
            },
            {
                headerText: "CTR",
                dataKey:'ctr',
                dataType: "number", 
                cellFormatter: function (args) {
                    if (args.row.type & $.wijmo.wijgrid.rowType.data) {
                        args.$container.html(args.formattedValue+'%');
                        return true;
                    }
                }
            },
            {
                headerText: "Bid",
                dataKey:'bid',
                dataType: 'string',
                allowSort:false,
                cellFormatter: function (args) {
                    if (args.row.type & $.wijmo.wijgrid.rowType.data) {
                        if ( args.row.data.campaign_level_targeting ) {
                            args.$container.html(args.formattedValue);
                            args.$container.closest("td")
                            .attr('id', "bid_"+args.row.data.id)
                            .attr('data-editable', "campaign-cpc")
                            .attr('data-type', args.row.data.campaign_type_id)
                            .attr('data-user', args.row.data.user_id)
                            .attr('data-campaign', args.row.data.id ).css({
                                "width": "55px",
                                "overflow":"hidden"
                            });
                        } else {
                            args.$container.html(args.row.data.cpc).css({
                                "width": "55px",
                                "overflow":"hidden"
                            });
                        }
                        return true;
                    }
                }
            },
            {
                headerText: "Costs",
                dataKey:'costs',
                dataType: "currency",
                
                cellFormatter: function (args) {
                    if (args.row.type & $.wijmo.wijgrid.rowType.data) {
                        args.$container.html(args.formattedValue);
                        args.$container.closest("td").css({
                            "width": "60px",
                            "overflow":"hidden"
                        });
                        return true;
                    }
                }
            },
            {
                headerText: "Status ID",
                dataKey:'status_id',
                dataType: "number", 
                dataFormatString: "n:0",
                allowSort:false,
                visible:false
                
            },
            {
                headerText: "Campaign Type ID",
                dataKey:'campaign_type_id',
                dataType: "number", 
                dataFormatString: "n:0",
                allowSort:false,
                visible:false
                
            },
            {
                headerText: "User ID",
                dataKey:'user_id',
                dataType: "number", 
                dataFormatString: "n:0",
                allowSort:false,
                visible:false
                
            },
            {
                headerText: "Avg CPC",
                dataKey:'cpc',
                dataType: "number", 
                dataFormatString: "n:0",
                allowSort:false,
                visible:false 
            },
            {
                headerText: "campaign_level_targeting",
                dataKey:'campaign_level_targeting',
                dataType: "number", 
                dataFormatString: "n:0",
                allowSort:false,
                visible:false
            },
            {
                headerText: "Created",
                dataKey:'created',
                dataType: "string",
                sortDirection: 'descending',
                visible:false
            }],
            
            rowStyleFormatter: function (args) {
                if ((args.state & $.wijmo.wijgrid.renderState.rendering) && 
                    (args.type & $.wijmo.wijgrid.rowType.data)) {
                args.$rows.attr('id',  'cmp_row_'+ args.data.id);
                //addClass('tcenter status_id_'+args.data.status_id+' {id: '+args.data.id+', status_id: '+args.data.status_id+'}');
                }
            }
        });
        //initialize grid ends

        return true;
    },
    
    bindCheckAll: function() {

        var self = this;
        var checkAllBox = $(this.selectorTable + ' thead div.chkbx');
        var allBoxes = $(this.selectorTable + ' tbody div.chkbx');
       
        checkAllBox.live('click',function(){
            var inp = $(this);

            if(inp.is('.checked'))
            {
                $(self.selectorTable + ' tbody div.chkbx').addClass('checked');
                inp.addClass('checked');
            }
            else
            {
                $(self.selectorTable + ' tbody div.chkbx').removeClass('checked');
                inp.removeClass('checked');       	
            }
        });

        allBoxes.live('click', function(){
            var inp = $(this);
            
            if(inp.is('.checked'))
            {
                checkAllBox.removeClass('checked');
                inp.addClass('checked');
            }
            else
            {
                inp.removeClass('checked');       	
            }            
        });
    },

    onCheckBoxChanged: function() {
        var self = this;
        $(self.selectorTable + ' tbody div.chkbx').live('click', function() {

            var total = $(self.selectorTable + ' tbody div.chkbx').length;
            var totalChecked = $(self.selectorTable + ' tbody div.chkbx.checked').length;
            if(total == totalChecked) {
                $(self.selectorTable + ' thead div.chkbx').addClass('checked');
            } else {
                $(self.selectorTable + ' thead div.chkbx').removeClass('checked');
            }

        });
    }
}

$.fn.enableCPCEdits = function(minCPCBid, minCPMBid, minCPABid)
{
    //don't allow read only user to change
    if($.inArray(2, window.accessRules) == -1 && $.inArray(3, window.accessRules) == -1) {
        return true;
    }
    
    var cpcFocus = function(e){
        $(this).select();
    };
    var cpcSave = function(e){
        var td = $(this).parents('td');
        var tbl = td.parents('table');
        
        var val = parseFloat($(this).val());
        var id = $(this).data('campaign');
        var col = td.index();
        var row = parseInt(td.data('index'));
        var numeric = (val - 0) == val;
        var user = td.data('user');
        
        // values are the same, don't update
        if(val == parseFloat(this.defaultValue))
        {
            return false;
        }
        
        var type = td.data('type');
        var min_bid = '0.00';
        if(type == 1){
           min_bid = minCPCBid;
        } else if (type == 2) {
           min_bid = minCPMBid;
        } else {
           min_bid = minCPABid;
        }

        if(numeric && val >= min_bid)
        {
            $.ajax({
                type: 'POST',
                url: '/advertiser/update-bid',
                data: {
                    'id':id,
                    'bid':val,
                    'user_id':window.userId,
                    'logged_in_user_id':window.loggedInUserId,
                    'main_user_id':window.mainUserId},
                success: function(response){
                    var data = tbl.wijgrid("data");
                    data[row][col] = val; // change data in grid
                }
            });   
        }
        else
        {
            $(this).val(parseFloat(this.defaultValue).toFixed(2));      
        }

    };

    var cpcChange = function(e){
        var validkeys = {
            27:'esc',
            38: 'up', 
            40:'down', 
            39:'right',
            37:'left',
            33:'pgup',
            34:'pgdn',
            107:'plus',
            109:'minus',
            110:'period',
            190:'period',
            13:'enter',
            9:'tab',
            46:'del',
            8:'bksp'
        }

        for(var x = 48;x<=57;x++) validkeys[x] = x;
        for(var x = 96;x<=105;x++)validkeys[x] = x;

        var val = parseFloat($(this).val());
        
        var type = $(this).parents('td').data('type');
        var min_bid = '0.00';
        
        if(type == 1){
           min_bid = minCPCBid;
        } else if (type == 2) {
           min_bid = minCPMBid;
        } else {
           min_bid = minCPABid;
        }

        if(validkeys[e.keyCode])
        { 
            switch(validkeys[e.keyCode])
            {
                case 'right':
                case 'plus':
                case 'pgup':
                case 'up':
                    $(this).val((val + 0.01).toFixed(2));
                    break;

                case 'left':
                case 'minus':
                case 'pgdn':
                case 'down':
                    if(val == min_bid) return;
                    $(this).val((val - 0.01).toFixed(2));
                    break;

                case 'enter':
                    $(this).trigger('blur');
                    e.preventDefault();
                    break;

                case 'esc':
                    $(this).val(parseFloat(this.defaultValue).toFixed(2));
                    e.preventDefault();
                    break;            

                case 'tab':
                case 'del':
                case 'bksp':
                default:
                    return true;
                    break;
            } 
        }
        return false;  
    }

    $(this).each(function(){
        var td = $(this);
        var val = $(this).text();
        var typeid = td.data('type');
        if(typeid == 1){
            var type = 'CPC';
        }else{
            var type = 'CPM';
        } 
        
        var ht = '<span class="campaigntype">'+type+'</span>';
        var sp = $('<input type="text" class="cpc-editable" value="'+val+'" data-campaign="'+td.data('campaign')+'" />');
        sp.bind('focus', cpcFocus);
        sp.bind('keydown', cpcChange);
        sp.bind('blur', cpcSave);        
        $('.wijmo-wijgrid-innercell', td).html(sp);	                                                               
    });
};


$.fn.enableDailyBudgetEdits = function(minBudget, maxBudget)
{
    //don't allow read only user to change
    if($.inArray(2, window.accessRules) == -1 && $.inArray(3, window.accessRules) == -1) {
        return true;
    }
    
    var dailyBudgetFocus = function(e){
        $(this).select();
    };
    
    var dailyBudgetSave = function(e){
        var self = this;
        var td = $(this).parents('td');
        var tbl = td.parents('table');
        
        var dailyBudget = parseFloat($(this).val().replace("$",""));
        var orgValue = parseFloat(this.defaultValue.replace("$",""));
        var campaignId = $(this).data('campaign_id');
        
        var colIndex = td.index();
        var rowIndex = parseInt(td.data('row_index'));
        var numeric = (dailyBudget - 0) == dailyBudget;
        var userId = td.data('user_id');
        
        // values are the same, don't update
        if(dailyBudget == orgValue) {
            $(self).val('$'+dailyBudget.toFixed(2));
            return false;
        }
        
        if(numeric && (dailyBudget >= minBudget) && (dailyBudget <= maxBudget) ) {
            $.ajax({
                type: 'POST',
                url: '/ajax/update-campaign-daily-budget',
                data: {'id':campaignId,
                    'daily_budget':dailyBudget,
                    'user_id':window.userId,
                    'logged_in_user_id':window.loggedInUserId,
                    'main_user_id':window.mainUserId},
                dataType:'json',
                success: function(response){
                    if (response.success) {
                        var data = tbl.wijgrid("data");
                        data[rowIndex][colIndex] = dailyBudget;
                        $(self).val('$'+dailyBudget.toFixed(2)); 
                    } else {
                        $(self).val('$'+orgValue.toFixed(2));     
                    }
                }
            });   
        } else {
            $(self).val('$'+orgValue.toFixed(2));      
        }
    };

    var dailyBudgetChange = function(e){
        var validkeys = {
            27:'esc',
            38: 'up', 
            40:'down', 
            39:'right',
            37:'left',
            33:'pgup',
            34:'pgdn',
            107:'plus',
            109:'minus',
            110:'period',
            190:'period',
            13:'enter',
            9:'tab',
            46:'del',
            8:'bksp'
        }

        for(var x = 48;x<=57;x++) validkeys[x] = x;
        for(var x = 96;x<=105;x++)validkeys[x] = x;

        var dailyBudget = parseFloat($(this).val().replace("$",""));
        
        if(validkeys[e.keyCode])
        { 
            switch(validkeys[e.keyCode])
            {
                case 'right':
                case 'plus':
                case 'pgup':
                case 'up':
                    $(this).val('$'+parseFloat(dailyBudget+1).toFixed(2));
                    break;

                case 'left':
                case 'minus':
                case 'pgdn':
                case 'down':
                    $(this).val('$'+parseFloat(dailyBudget-1).toFixed(2));
                    break;

                case 'enter':
                    $(this).trigger('blur');
                    e.preventDefault();
                    break;

                case 'esc':
                    $(this).val('$'+parseFloat(this.defaultValue.replace("$","")).toFixed(2));
                    e.preventDefault();
                    break;            

                case 'tab':
                case 'del':
                case 'bksp':
                default:
                    return true;
                    break;
            } 
        }
        return false;  
    }

    $(this).each(function(){
        var td = $(this);
        var val = parseFloat($(this).text().replace(',','').replace("$",""));
        var html = $('<input type="text" class="campaign-daily-budget-editable" value="$'+val.toFixed(2)+'" data-campaign_id="'+td.data('campaign_id')+'" />');
        html.bind('focus', dailyBudgetFocus);
        html.bind('keydown', dailyBudgetChange);
        html.bind('blur', dailyBudgetSave);        
        $('.wijmo-wijgrid-innercell', td).html(html);	                                                               
    });
};


$.initialize = function() { 
    // Update
    if ($.browser.msie  && parseInt($.browser.version, 10) === 8) {
    // IE8 doesn't like HTML!
    } else {
        window.addEventListener("load", function (a) {
            window.applicationCache.addEventListener("updateready", function (a) {
                if (window.applicationCache.status == window.applicationCache.UPDATEREADY) {
                    window.applicationCache.swapCache();
		            
                    $.notification( 
                    {
                        title: 'An update has been installed!',
                        content: 'Click here to reload.',
                        icon: "u",
                        click: function() {
                            window.location.reload();
                        }
                    }
                    );
		            
                }
            }, false);
	    
            window.applicationCache.addEventListener("downloading", function (a) {
                if (window.applicationCache.status == window.applicationCache.DOWNLOADING) {
                    $.notification( 
                    {
                        title: 'Latest version is being cached',
                        content: 'Only takes a few seconds.',
                        icon: "H"
                    }
                    );
                }
            }, false);

        }, false);
    }
    // Adding overlay to the body
    $("body").append('<div id="overlays"></div>');
	
        
    //Display of Notication for Filling the Payment info
    var paymentMsg = '';
    if(!window.paymentInfoUpdated)
        paymentMsg = '<div class="cleanmessage">You haven\'t completed your payment information yet. <a href="/account/info" target="_blank">Click here to complete.</a> </div>' ;
             
    if(paymentMsg!='') {
        $.notification( 
        {
            title: paymentMsg,
            border: false,
            img: "/images/warning_icon.png"
        }
        );
    }    
         
    // Welcome notification  
    if (advFunds < 50) {
        var msg = '<div class="cleanmessage"><p class="message50">Your account balance is $0.00. <a href="/account/summary">Click here to add funds.</a></p></div>';
        var img =  "/images/warning_icon.png";    
        var isError = true;
        if ( advFunds > 0 ) {
            msg = '<div class="cleanmessage"><p class="message50">Your account balance is $'+parseInt(advFunds).toFixed(2).replace(/^(\d{1,})(\d{3})(.*)/, "\$1,\$2\$3")+' <a href="/account/summary">Add more funds.</a></p></div>';
            img = "/images/funds.png";
            isError = false;
        } 
        $.notification({
            title: msg,
            border: false,
            img:img,
            error:isError
        });
    }
    
    var emsg = '';        
    if(window.emailMsgShow == '1') {				
        emsg = '<div class="cleanmessage">'+
    '<p class="message0">Please check your email for the link to activate your account &nbsp;&nbsp;<a href="javascript:void(0);" id="resendLink" title="Click here to Resend Activation Email">( Resend Activation Email )</a></p>'+                                                        
    '</div>';
    } else if(repeatMsg == "1")
        emsg = '<div class="cleanmessage"><p class="message0"><a href="javascript:void(0);" id="resendLink">Click</a>  here to resend Activation email</p></div>';	

    if(emsg!='') {
        $.notification( 
        {
            title: emsg,
            border: false,
            img: "/images/mail.png"
        }
        );
                    
    }              
}

var statusChange = {
    init: function() {
        $('.status_name').click(function() {
			
            //don't allow read only user to change
            if($.inArray(2, window.accessRules) == -1 && $.inArray(3, window.accessRules) == -1) {
                return true;
            }             
                        
            if($(this).find('select').length) {
                return false;
            }

            if($(this).hasClass('no_edit')) {
                return false;
            }								
				
            if(c == 0){
                var oldStatusName = $(this).text();
                $(this).find("ul").remove();
            
                $(this).append($('#campaign_status_options ul').clone());
                c = 1;
                return true;
            }
            else
            {
                var oldStatusName = $(this).text();
                $(this).find("ul").remove();
            
                c = 0;
                return true;
            }
        });
    },
    bindOnOptionChanged: function(statusId, campaignId, statusName) {	
        //don't allow read only user to change
        if($.inArray(2, window.accessRules) == -1 && $.inArray(3, window.accessRules) == -1) {
            return true;
        }  
        
        var confirmResult;
        if(statusId == 9){
            jConfirm('Are you sure you want to complete this action?', 'Confirm Action', function(j) {
                if(j) {                    
                    if(statusId == -1) { 
                        $this.selectBox('destroy');
                        $this.parent().html($this.metadata().oldName);
                    } else {	
                        statusChange.sendRequest(statusId, campaignId, statusName);
                    }                     
                }
            });   
        } else {  
            statusChange.sendRequest(statusId, campaignId, statusName);
        }                    
    },

    updateCampaignStatusOptions: function(campaignId, statusId, statusName) {
        //set image and new status name in td.
        var campaignStatusOptionsDiv = $("#campaign_status_options_"+campaignId);
        var campaignStatusNameDiv = $(campaignStatusOptionsDiv).siblings('div.status_name');
        
        //update status name
        var imageHtml = "<img src='/images/ico-" + statusName.toLowerCase() + ".png' class='status_icon'>"+ statusName;
        $(campaignStatusNameDiv).find('.bg').html(imageHtml);
                          
        //build the li's
        var statusOptions = '';
                        
        //run li
        if ( statusId != 6 && ( window.__k || statusId == 2 || statusId == 3 || statusId == 7) ) {
            statusOptions += '<li id ="status_option_6_'+campaignId+'" onclick="statusChange.bindOnOptionChanged(6, '+campaignId+', \'Running\');"><img src="/images/ico-running.png" />Run</li>'; 
        } 
        //pause li
        if ( statusId == 6 || statusId == 10 ) {
            statusOptions += '<li id ="status_option_7_'+campaignId+'" onclick="statusChange.bindOnOptionChanged(7, '+campaignId+', \'Paused\');"><img src="/images/ico-paused.png" />Pause</li>'; 
        }
        //decline li
        if ( statusId != 9 && statusId != 4 && window.__k ) {
            statusOptions += '<li id ="status_option_4_'+campaignId+'" onclick="statusChange.bindOnOptionChanged(4, '+campaignId+', \'Declined\');"><img src="/images/ico-declined.png" />Declined</li>'; 
        }
        
        //delete li
        if ( statusId != 9 ) {
            statusOptions += '<li id ="status_option_9_'+campaignId+'" onclick="statusChange.bindOnOptionChanged(9, '+campaignId+', \'Deleted\');"><img src="/images/ico-deleted.png" />Delete</li>'; 
        }               
        
        $(campaignStatusOptionsDiv).html("<ul class=\"cso_items\">"+statusOptions+"</ul>");
    },

    sendRequest: function(statusId, campaignId,statusName) {    
        //don't allow read only user to change
        if($.inArray(2, window.accessRules) == -1 && $.inArray(3, window.accessRules) == -1) {
            return true;
        }
        
        //updateCampaignStatus
        $.ajax({
            type: 'POST',
            url: '/advertiser/update-campaign-status',
            data: {
                'campaign_id': campaignId, 
                'status_id': statusId,
                'user_id':window.userId,
                'logged_in_user_id':window.loggedInUserId,
                'main_user_id':window.mainUserId
            },
            success: function(r) {               
                var $tr = $('#cmp_row_' + r.items[0].id );
                var foundTr = false;							
                if ( $tr.attr('id') == 'cmp_row_' + r.items[0].id ) {
                    foundTr = true;
                } 
                 
                if ( foundTr ) {
                                                                                
                    if(statusId == 9) {                        
                        //remove the row after delete
                        $tr.remove();
                       
                    } else {
                        statusChange.updateCampaignStatusOptions(campaignId,statusId,statusName);
                    }
                }              
            // do not reload the page
            // $('#chart_filter_status').change();
            }
        })
    }
}

var campaignChange = {

    tableSelector: '#advertiser_dashboard_campaigns_list', 
    init: function() {

        var self = this;
        $('#change_campaign').selectBox({});
        
        //don't allow read only user to change
        if($.inArray(2, window.accessRules) == -1 && $.inArray(3, window.accessRules) == -1) {
            $('#change_campaign').selectBox('disable');
        }
        
        $('#change_campaign').change(function(){
            
            //don't allow read only user to change
            if($.inArray(2, window.accessRules) == -1 && $.inArray(3, window.accessRules) == -1) {
               return true;
            }
            
            //To check Campaign is selected to perform action
            var selectedCampaigns = self.getSelectedCampaignIds();
            if (selectedCampaigns.length == 0) {
                jAlert('Please select a Campaign to perform action');
                $(this).selectBox('value','0');
                return false;
            }

            var statusId = $(this).val();
            
            if(statusId > 0) {
                self.editStatus(statusId,selectedCampaigns);
            } else if(statusId == -2) {
                self.copyCampaign(selectedCampaigns);
            } else if(statusId == -1) {
                self.editCampaign(selectedCampaigns);
            }
            
            $(this).selectBox('value','0');

        });

        this.bindCancelEdit();
        this.bindSaveCampaignName();
        this.bindCheckAll();
    },

    editStatus: function(statusId, selectedCampaigns) { 
        
        //don't allow read only user to change
        if($.inArray(2, window.accessRules) == -1 && $.inArray(3, window.accessRules) == -1) {
            return true;
        }
        
        jConfirm('Are you sure you want to complete this action?', 'Confirm Action', function(j) {
            if(j) {

                $.ajax({
                    url: '/advertiser/update-campaign-status',
                    data: {
                        'campaign_id': selectedCampaigns.join('_'),
                        'status_id': statusId,
                        'user_id':userId,
                        'logged_in_user_id':window.loggedInUserId,
                        'main_user_id':window.mainUserId
                    },
                    success: function(response) {                        
                        $.each(response.items, function(i, elem) {

                            if(elem.status_id == 9) {
                                $('.r_campaign_' + elem.id).remove();
                            //$('#cmp_row_' + elem.id).parents('tr').remove();
                            } else {
                                $('#cmp_row_' + elem.id).find('td.status_name').html('<span class="bg">' + elem.status + '</span>');
                                //  change status_id class

                                var $statusNameTd = $('#cmp_row_' + elem.id).find('td.status_name');
                              
                                //make sure to call meta data on non object 
                                if ( $($statusNameTd).attr('class') == 'status_name' ) {
                                    var currentStatusId = $statusNameTd.metadata().status_id;
                                    $('#cmp_row_' + elem.id).removeClass('status_id_' + currentStatusId);
                                    $statusNameTd.metadata().status_id = statusId;
                                }
                                
                                $('#cmp_row_' + elem.id).addClass('status_id_' + statusId)

                            }

                        });
                        $.each(selectedCampaigns, function (i, elem){
                            $('#cmp_row_' + elem).find('input[type=checkbox]')
                            .removeAttr('checked')
                            .parent().removeClass('checked');
                        });
                        
                        

                        $('#chart_filter_status').change();                     
                    }
                });
            }
        });
        return true;
    },

    getSelectedCampaignIds: function() {
        var ids = [];
        $.each($('#advertiser_dashboard_campaigns_list tbody div.chbx_campaign'), function() {
            if($(this).hasClass('checked')) {
                ids.push($(this).metadata().id);
            }
        });
		
        return ids;
    },

    copyCampaign: function(ids) {
        //don't allow read only user to change
        if($.inArray(2, window.accessRules) == -1 && $.inArray(3, window.accessRules) == -1) {
            return true;
        }
        
        
        var self = this;     
        var params = {
            'ids': ids.join('_'),
            'user_id':window.userId,
            'logged_in_user_id':window.loggedInUserId,
            'main_user_id':window.mainUserId};
        if (window.requestId) {
            var params = {
                'ids': ids.join('_'), 
                'user_id':window.requestId,
                'logged_in_user_id':window.loggedInUserId,
                'main_user_id':window.mainUserId
                };
        }
        $.ajax({
            url: '/advertiser/copy-campaign',
            type: 'POST',
            data: params,
            success: function(response) {
                
                if(response.campaigns) {
                    self.renderNewCampaigns(response.campaigns);
                }

                $.each(ids, function (i, elem){
                    $('#cmp_row_' + elem).find('input[type=checkbox]')
                    .removeAttr('checked')
                    .parent().removeClass('checked');
                });

                //reload only campaign grid
                if (response.campaigns) {
                    campaignsList.getItems();
                }
            }
        });
        return true;
    },

    renderNewCampaigns: function(data) {
        var self = this;
        $.each(data, function(originalId, campaigns){

            $.each(campaigns, function(i, elem){
                self.renderCampaign(originalId, elem);
            });
        });
    },

    renderCampaign: function(afterId, data) {

        var html =
        '<tr id="cmp_row_'+data.id+'" class="tcenter tbold campaign_item status_id_1 r_campaign_'+data.id+' {id: '+data.id+'}" style="height:30px;">' +
        '<td><div class="chkbx"><input type="checkbox" class="cmp_item chbx_campaign {id:'+data.id+'}" style="display:none;"></div></td>' +
        '<td class="add clickable"><a href="/advertiser/campaign-edit-overview/id/'+data.id+'">'+data.name+'</a></td>' +
        '<td class="status_name  {status_id: 1}">Pending</td>' +
        '<td>$0.00</td>' +
        '<td>0</td>' +
        '<td>0</td>' +
        '<td>0</td>' +
        '<td>0.00%</td>' +
        '<td>$0.00</td>' +
        '<td>'+data.cpc_bid+'</td>' +
        '<td>$0.00</td>' +
        '</tr>';

        $('tbody').prepend(html);
    },


    editCampaign: function(ids) {
        //don't allow read only user to change
        if($.inArray(2, window.accessRules) == -1 && $.inArray(3, window.accessRules) == -1) {
            return true;
        }
        
        var self = this;       

        $.each(ids, function(i, elem){
            var item = $('#cmp_row_' + elem);
            var td = item.children('td:first').next('td'); //  second column with name
            var form = $('form', td);
            var width = td.innerWidth();
            
            if(form.length) {
                form.remove();
            }
            
            var editFormHtml = '\
            <form style="padding:3px"> \
            	<input type="hidden" value="'+elem+'"/> \
            	<input type="text" style="width:98%" value="' + $.trim(td.text()) + '"> \
            	<br/><a class="save_campaign_name" href="javascript:void(0);">Save</a> \
            	<a class="cancel_campaign_edit {name: \'' + $.trim(td.text())+ '\'}" href="javascript:void(0);">Cancel</a> \
            </form>';
            
            td.find('div').hide();
            td.append(editFormHtml);
            
            // stupid wijmo readonly crap
            $('input[type=text]',td).mousedown(function(e){
                e.stopImmediatePropagation()
            });
        });

        $.each(ids, function (i, elem){
            $('#cmp_row_' + elem).find('input[type=checkbox]')
            .removeAttr('checked')
            .parent().removeClass('checked');
        });

        return true;
    },

    bindCancelEdit: function() {

        $('td form a.cancel_campaign_edit').live('click', function(e){

            var td = $(this).parents('td:first');
            $('form', td).remove();
            $('div', td).show();

            return false;
        });
    },

    bindCheckAll: function() {
			
        var self = this;
        var checkAllBox = $(this.tableSelector + ' thead div.chkbx');
        var allBoxes = $(this.tableSelector + ' tbody div.chkbx');
		 
        checkAllBox.live('click',function(){
				
            var inp = $(this);
            if(inp.is('.checked'))
            {
                $(self.tableSelector + ' tbody div.chkbx').addClass('checked');
                inp.addClass('checked');
            }
            else
            {
                $(self.tableSelector + ' tbody div.chkbx').removeClass('checked');
                inp.removeClass('checked');       	
            }
        });

        allBoxes.live('click', function(){
            var inp = $(this);					
            if(inp.is('.checked'))
            {
                checkAllBox.removeClass('checked');
                inp.addClass('checked');
            }
            else
            {
                inp.removeClass('checked');       	
            }            
        });
    },
	
    bindSaveCampaignName: function() {

        $('td form a.save_campaign_name').live('click', function(e) {

            //don't allow read only user to change
            if($.inArray(2, window.accessRules) == -1 && $.inArray(3, window.accessRules) == -1) {
                return true;
            }

            var td =   $(this).parents('td');
            var idx = td.parent().index();
            var id =   $('input[type=hidden]',td).val();
            var name = $.trim($('input[type=text]',td).val());

            $.ajax({
                url: '/advertiser/rename-campaign',
                type: 'POST',
                data: {
                    'id': id,
                    'name': name,
                    'user_id':window.userId,
                    'logged_in_user_id':window.loggedInUserId,
                    'main_user_id':window.mainUserId
                },
                success: function(r) {
                    if(r.updated) {
                        $('form', td).remove();
                        $('div > a', td).text(name);
                        $('div', td).show();
								
                        // update wijgrid
                        var tbl = $("#advertiser_dashboard_campaigns_list");
                        var data = tbl.wijgrid('data');
                        data[idx][2] = '<a href="/advertiser/campaign-edit-overview/id/'+id+'">'+name+'</a>';
                        tbl.wijgrid('ensureControl', true);
                    }
                }
            });

            e.stopPropagation();
            return false;
        });
    }
}


var statusChange = {

    init: function() {
        $('.status_name').click(function() {

            //don't allow read only user to change
            if($.inArray(2, window.accessRules) == -1 && $.inArray(3, window.accessRules) == -1) {
                return true;
            }

            if($(this).find('select').length) {
                return false;
            }
            if($(this).hasClass('no_edit')) {
                return false;
            }									
				
            if(c == 0){
                var oldStatusName = $(this).text();
                $(this).find("ul").remove();
            
                $(this).append($('#campaign_status_options ul').clone());
                c = 1;
                return true;
            }
            else
            {
                var oldStatusName = $(this).text();
                $(this).find("ul").remove();
            
                c = 0;
                return true;
            }
        });
    },
    bindOnOptionChanged: function(statusId, campaignId, statusName) {	
        
        //don't allow read only user to change
        if($.inArray(2, window.accessRules) == -1 && $.inArray(3, window.accessRules) == -1) {
            return true;
        }
        
        var confirmResult;
        if(statusId == 9){
            jConfirm('Are you sure you want to complete this action?', 'Confirm Action', function(j) {
                if(j) {                    
                    if(statusId == -1) { 
                        $this.selectBox('destroy');
                        $this.parent().html($this.metadata().oldName);
                    } else {	
                        statusChange.sendRequest(statusId, campaignId, statusName);
                    }                     
                }
            });   
        } else {  
            statusChange.sendRequest(statusId, campaignId, statusName);
        }                    
    },

    updateCampaignStatusOptions: function(campaignId, statusId, statusName) {
        //set image and new status name in td.
        var campaignStatusOptionsDiv = $("#campaign_status_options_"+campaignId);
        var campaignStatusNameDiv = $(campaignStatusOptionsDiv).siblings('div.status_name');
        
        //update status name
        var imageHtml = "<img src='/images/ico-" + statusName.toLowerCase() + ".png' class='status_icon'>"+ statusName;
        $(campaignStatusNameDiv).find('.bg').html(imageHtml);
                          
        //build the li's
        var statusOptions = '';
                        
        //run li
        if ( statusId != 6 && ( window.__k || statusId == 2 || statusId == 3 || statusId == 7) ) {
            statusOptions += '<li id ="status_option_6_'+campaignId+'" onclick="statusChange.bindOnOptionChanged(6, '+campaignId+', \'Running\');"><img src="/images/ico-running.png" />Run</li>'; 
        } 
        //pause li
        if ( statusId == 6 || statusId == 10 ) {
            statusOptions += '<li id ="status_option_7_'+campaignId+'" onclick="statusChange.bindOnOptionChanged(7, '+campaignId+', \'Paused\');"><img src="/images/ico-paused.png" />Pause</li>'; 
        }
        //decline li
        if ( statusId != 9 && statusId != 4 && window.__k ) {
            statusOptions += '<li id ="status_option_4_'+campaignId+'" onclick="statusChange.bindOnOptionChanged(4, '+campaignId+', \'Declined\');"><img src="/images/ico-declined.png" />Declined</li>'; 
        }
        
        //delete li
        if ( statusId != 9 ) {
            statusOptions += '<li id ="status_option_9_'+campaignId+'" onclick="statusChange.bindOnOptionChanged(9, '+campaignId+', \'Deleted\');"><img src="/images/ico-deleted.png" />Delete</li>'; 
        }               
        
        $(campaignStatusOptionsDiv).html("<ul class=\"cso_items\">"+statusOptions+"</ul>");
    },

    sendRequest: function(statusId, campaignId,statusName) {                 
        
        //don't allow read only user to change
        if($.inArray(2, window.accessRules) == -1 && $.inArray(3, window.accessRules) == -1) {
            return true;
        }
        
        //updateCampaignStatus
        $.ajax({
            type: 'POST',
            url: '/advertiser/update-campaign-status',
            data: {
                'campaign_id': campaignId, 
                'status_id': statusId,
                'user_id':userId,
                'logged_in_user_id':window.loggedInUserId,
                'main_user_id':window.mainUserId
            },
            success: function(r) {               
                var $tr = $('#cmp_row_' + r.items[0].id );
                var foundTr = false;							
                if ( $tr.attr('id') == 'cmp_row_' + r.items[0].id ) {
                    foundTr = true;
                } 

                if ( foundTr ) {
                                                                                
                    if(statusId == 9) {                        
                        //remove the row after delete
                        $tr.remove();
                    } else {
                        statusChange.updateCampaignStatusOptions(campaignId,statusId,statusName);
                    }
                }              
            // do not reload the page
            // $('#chart_filter_status').change();
            }
        })

    }
}

$(document).ready(function(){
    if ( window.continueRegistration ) {
        var captchaError = '';
        if($('#captchaError').length){
            captchaError = '?captcha_error=1'
        }
       
        $.fn.modal({
            theme:      "dark",
            layout:     undefined,
            url:        '/index/continue-registration/id/'+window.mainUserId+captchaError,
            content:    undefined,
            animation:  "flipInX"
        });
    }
  
});