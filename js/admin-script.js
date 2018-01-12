jQuery(document).ready(function($){
	$('.wbinr_tabs li a').click(function(){
		$('.wbinr_tabs li a').removeClass('selected');
		$(this).addClass('selected');
		var id = $(this).attr('href').replace('#', '' );
		$('.wbinr_tab_content li').hide();
		$('#wbinr_tab_'+id).show();
		return false;
	});
	
	$('.wbinr_tabs li:first-child a').trigger('click');
	
	$('#schedule_type').change(function(){
		var val = $(this).val();
		$(this).parent().parent().find('.wbinr_schedule_fld.day').hide();
		if( val == 'every'){
			$(this).parent().parent().find('.wbinr_schedule_fld.schedule_day').show();
		}else{
			$(this).parent().parent().find('.wbinr_schedule_fld.schedule_date').show();
		}
	});
	
	var dateToday = new Date(); 
	$( "#schedule_date" ).datepicker({
    	minDate: 0,
		dateFormat: "dd-mm-yy",
		showOn: 'both',
		buttonText: '<i class="fa fa-calendar" aria-hidden="true"></i>'
	});
	
	$('#schedule_time').wickedpicker();
	$('#schedule_time').val('');
	
	$('.wbinr_page_select').change(function(){
		var val = $(this).val(),
			page = $(this).attr('data-page');
		$(this).parent().find('.wbinr_page_url').hide();
		$('.wbinr_page_url.'+page+'.'+val).show();		
	});
	
	$('#wbinr_add_schedule').click(function(){
		var type = $('#schedule_type').val();		
		var reason = "";
		if( type == 'every' ){
			reason += WebinarPress.form.validateEmpty( 'schedule_day', "Required." );			
		}else{
			reason += WebinarPress.form.validateEmpty( 'schedule_date', "Required." );
		}
		reason += WebinarPress.form.validateEmpty( 'schedule_time', "Required." );
		
		if( reason == ""  ) {
			var html = $('#wbinr_schedule_repeat').html(),
				$row = $('<div class="wbinr_schedule_repeat">'+ html + '</div>'),
				type = $('#schedule_type').val(),				
				day  = $('#schedule_day').val(),
				date = $('#schedule_date').val(),
				time = $('#schedule_time').val();
			
			$row.find('.schedule_type').val(type).removeAttr('id').removeAttr('name').attr('disabled', 'disabled' );
				
			if( type == 'every' ){
				$row.find('.schedule_day').val(day).removeAttr('id').removeAttr('name').attr('disabled', 'disabled' );	
				$row.append('<input type="hidden" value="'+day+'" name="schedule_day[]" >');	
				//$row.find('.schedule_date').remove();				
			}else{
				$row.find('.schedule_date').val(date).removeAttr('id').attr('readonly', 'readonly' );	
				//$row.find('.schedule_day').remove();	
			}
			$row.find('.schedule_time').val(time).removeAttr('id').attr('readonly', 'readonly' );	
			$row.find('.wbinr_input_error').remove();
			
			$row.append('<input type="hidden" value="'+type+'" name="schedule_type[]" >');
			
			$row.append('<a href="#" class="wbinr_remove_schedule"><i class="fa fa-trash" aria-hidden="true"></i></a>');	
						
			$('#wbinr_schedules').append($row);
			$('#schedule_type').val( 'every' );			
			$('#schedule_day').val( 'Day' );
			$('#schedule_date').val( '' );
			$('#schedule_time').val( '' );
			
			$('#schedule_type').trigger('change');
		}
		return false;
	});
	
	$(document).on('click', '.wbinr_remove_schedule', function(){
		$(this).parent().remove();
		return false;
	});
});

var WebinarPress = {
	tabs:{
		moveTo: function ( tab_name ){
		}
	},
	html :{
		
	},
	form : {		
		data: {
			addListing:false,
		},
		trim: function(s){
			return s.replace(/^\s+|\s+$/, '');
		},		
		validateEmpty: function (field,msg) {
			var error = "",
				fld=document.getElementById(field);
			if (fld.value.length == 0 ) {
				error = msg;
				this.display_msg(field,msg);
			} else {
				this.clr_msg(field,msg);
			}
			return error;  
		},
		
		validatePhone: function (field,msg) {
			var error = "",
				fld=document.getElementById(field),
				matcher= /^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/;
			if (fld.value.length == 0 ) {
				this.clr_msg(field,msg);
			} else {
			if(!fld.value.match(matcher)) {
				error = msg;
				this.display_msg(field,msg);
			} else {
				this.clr_msg(field,msg);
			}
			}
			return error;  
		},
		
		
		validateEmail: function (field,msg) {
			var error="",
				fld = document.getElementById(field),
				tfld = this.trim(fld.value),
				emailFilter = /^[^@]+@[^@.]+\.[^@]*\w\w$/ ,
				illegalChars= /[\(\)\<\>\,\;\:\\\"\[\]]/ ;
			if (!emailFilter.test(tfld)) {              //test email for illegal characters
				error = msg;
				this.display_msg(field,error);
			} else if (fld.value.match(illegalChars)) {
				error = msg;
				this.display_msg(field,error);
			} else{       
				this.clr_msg(field,msg);
			}
			return error;
		},
		
		validate_num: function ( field, msg ) {
			var error = "",
				fld=document.getElementById(field),
				stripped = fld.value.replace(/[\(\)\.\-\ ]/g, '');
										
		   	if (isNaN(parseInt(stripped))){
				error = msg;
				this.display_msg(field,error);
			}else {        
				this.clr_msg(field,msg);
			}
			return error;
		},
		
		con_pass_check: function ( field, field1, msg){
			var error = "",
				fld=document.getElementById(field),
				fld1=document.getElementById(field1);			
		   	if (fld.value.length==0 || fld1.value.length==0 || fld.value!=fld1.value  ) {
				error = msg;
				this.display_msg(field,error);
			}else {       
				this.clr_msg(field,msg);
			}
			return error;
		},
		
		validate_fixedLength: function ( field, len, msg ) {
			var error = ""; 
			var fld = document.getElementById( field );
			var stripped = fld.value.replace(/[\(\)\.\-\ ]/g, '');    
			var len1 = parseInt( len );
			if (!( stripped.length == len1 ) ) {
				error = msg;
				this.display_msg( field, error );
			}else {
				this.clr_msg( field, msg );
			}
			return error;
		},
		
		is_checked: function(field,msg){
			var error = "";
			if(!jQuery('#'+field).is(":checked")){
				error = msg;
				this.display_msg(field,error);
			}else {
				this.clr_msg(field,msg);
			}
			return error;
		},
		
		display_msg: function (field,msg){
			var divname = field+"_error";
			if(WebinarPress.form.data.addListing){
				jQuery('#'+field).parent().addClass('error_input');
			}else{
				jQuery('#'+field).addClass('error_input');
			}
			jQuery('#'+divname).addClass('row_error').html(msg);
		},
				
		clr_msg: function (field,msg){
			var divname=field+"_error",
				div_text=jQuery('#'+divname).html();
			if( msg == div_text ){
				jQuery('#'+divname).removeClass('row_error').html('');
				if(WebinarPress.form.data.addListing){
					jQuery('#'+field).parent().removeClass('error_input');
				}else{
					jQuery('#'+field).removeClass('error_input');
				}
			}			
		}
	} 
};