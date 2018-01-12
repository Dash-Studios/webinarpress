jQuery(function($){	
	var ts = (new Date()).getTime() + wbinr.next_schedule * 1000;		
	$('.countdown').countdown({
		timestamp	: ts,
		callback	: function(days, hours, minutes, seconds){
		},		
		onComplete: function(){
			document.location.reload();
		}
	});
	
	$('.wbinr_close').click(function(){
		var id = $(this).attr('href');
		$(id).hide();
		return false;
	});
	
	$('.wbinr_register_trigger').click(function(){
		var id = $(this).attr('href');
		$(id).show();
		return false;
	});
	
	//submit webinar register form
	$('#wbinr_register_form').submit(function(){
		var that = $(this),
			button = $('#wbinr_register'),
			btnVal = button.val(),
			data = WebinarPress.input.serializeToObject( $( this ).serialize() );
			//console.log(data); return false;
			var reason = ""; 
			
			reason += WebinarPress.form.validateEmpty( 'wbinr_schedules_dropdown', 'Please select schedule' );
			reason += WebinarPress.form.validateEmpty( 'wbinr_user_name', 'Please enter your name.' );
			reason += WebinarPress.form.validateEmail( 'wbinr_user_email', 'Please enter valid email.' );
			reason += WebinarPress.form.validateEmpty( 'wbinr_user_email', 'Please enter your email.' );

			if( reason == "" ){
				$(button).val('Please wait..').attr('disabled', 'disabled');
				//$('.loading_on_body').show();
				console.log(data);
				WebinarPress.ajax( 'Register', data, function( response ){
					console.log(response);
					that.find('.error').html('');
					if(response.error){
						$('#wbinr_register_error').html( response.msg ).addClass('wbinr_error').removeClass('wbinr_success');
					}else{
						$('#wbinr_register_error').html( response.msg ).addClass('wbinr_success').removeClass('wbinr_error');
						if( response.redirect_url){
							document.location = response.redirect_url;
						}						
					}
					$(button).val(btnVal).removeAttr('disabled', 'disabled');
				});
			}
		return false;
	});
});
var WebinarPress = {
	ajax: function( action, data, callback ){
		jQuery.post(
			wbinr.ajaxurl,	{
				action : 'wbinrAjax',
				wbinrAction : action,
				data: JSON.stringify(data)			
			},
			function(res){
				callback(eval('(' +res+ ')'));			
			}
		);
	},
	
	fn:{
		
	},
	input : {
		serializeToObject: function( serialize ){
			var fields = serialize.split("&"),
				object = {};
			for(var i in fields){
				var field = fields[i].split("=");
				field[0] = field[0].replace("%5B%5D","");
				if ( ! object[field[0]] ){
					object[field[0]] = decodeURIComponent(field[1]);
				}else{
					if ( object[field[0]].constructor === Array ){
						object[field[0]].push(field[1])
					}else{
						object[field[0]] = [object[field[0]],field[1] ];
					}
				}
				//alert(decodeURIComponent(field[1]));
			}
			return object;
		}
	},
	
	form : {		
		data: {
			use_parent:false,
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
			if(this.data.use_parent){
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
				if(this.data.use_parent){
					jQuery('#'+field).parent().removeClass('error_input');
				}else{
					jQuery('#'+field).removeClass('error_input');
				}
			}			
		}
	}
};