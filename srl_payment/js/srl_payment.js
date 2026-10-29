jQuery(document).ready(function () {	
		
	jQuery('.srl-payeezy-payment-form .enrolment_agreement_error_message').hide();
	jQuery('.srl-payeezy-payment-form #accept-agreements').css('pointer-events','none');
	jQuery('.srl-payeezy-payment-form #accept-agreement').css('pointer-events','none');
	jQuery('#myfirstmodal #accept-agreements').click(function(){
		console.log('accepted');
		jQuery('.srl-payeezy-payment-form input[name=term_of_use]').val('Y');
		jQuery('.term-of-use .term-validate').show();
		jQuery('.term-of-use span.msg-agreement').hide();
		var termofuse = jQuery('.srl-payeezy-payment-form input[name=term_of_use]').val();
		var enrollment_agreement = jQuery('.srl-payeezy-payment-form input[name=enrollment_agreement]').val();
		if(termofuse == 'Y' && enrollment_agreement == 'Y'){
			if(jQuery('#edit-cohort-date').hasClass('form-select')){
				var value_date = jQuery('#edit-cohort-date option:selected').val();
			}else{
				var value_date = 'field not found';
			}
			if(value_date.length > 0){

				jQuery('#errormodal .modal-header button').click();
			}
		}
	});
	jQuery('#srl-proceed-payment-form-wrapper .btnscrollpayment').bind('click',function(){
		jQuery('.paymentoptions').removeClass('active');
		var classID = jQuery(this).data('attr');
		console.log(classID);
		jQuery('.' + classID+'.paymentoptions').addClass('active');
	});
	jQuery('#myfirstmodal #reject-agreements').click(function(){
		jQuery('.srl-payeezy-payment-form input[name=term_of_use]').val('N');
		jQuery('.term-of-use .term-validate').hide();
		jQuery('.term-of-use span.msg-agreement').show();
		jQuery('.srl-payeezy-payment-form .enrolment_agreement_error_message').show();
		jQuery('.srl-payeezy-payment-form a.error-popup-button').click();
	});
	jQuery('#myModal #accept-agreement').click(function(){
		console.log('accept');
		jQuery('.srl-payeezy-payment-form input[name=enrollment_agreement]').val('Y');
		var termofuse = jQuery('.srl-payeezy-payment-form input[name=term_of_use]').val();
		var enrollment_agreement = jQuery('.srl-payeezy-payment-form input[name=enrollment_agreement]').val();
		if(termofuse == 'Y' && enrollment_agreement == 'Y'){
			if(jQuery('#edit-cohort-date').hasClass('form-select')){
				var value_date = jQuery('#edit-cohort-date option:selected').val();
			}else{
				var value_date = 'field not found';
			}
			if(value_date.length > 0){
				jQuery('#errormodal .modal-header button').click();
			}
		}
	});
	jQuery('#myModal #reject-agreement').click(function(){
		console.log('reject');
		jQuery('.enrollment-agreement .agreement-validate').hide();
		jQuery('.enrollment-agreement span.msg-agreement').show();
		jQuery('.srl-payeezy-payment-form input[name=enrollment_agreement]').val('N');
		jQuery('.srl-payeezy-payment-form .enrolment_agreement_error_message').show();
		jQuery('.srl-payeezy-payment-form a.error-popup-button').click();
	});
	jQuery('a.myModal_unset').click(function(){
		if(jQuery('#edit-cohort-date').hasClass('form-select')){
			var value_date = jQuery('#edit-cohort-date option:selected').val();
		}else{
			var value_date = 'field not found';
		}
		if (value_date.length == 0) {
			jQuery('#edit-cohort-date').addClass('error');
			jQuery('#edit-cohort-date').focus();
			jQuery('#class-date-form .date-select-error').text('Please select class start date.');
		}else{
			
			jQuery("#myModal").modal('show');
		}
	});
	jQuery('#edit-cohort-date').on('change',function(){
		console.log('change date32');
		// jQuery("#edit-finance-options option[value='SFO']").attr("selected",true);
		// jQuery("#edit-finance-options").val('SFO').change();
		jQuery('#edit-cohort-date').removeClass('error');
		jQuery('#class-date-form .date-select-error').text('');
			var value_date = jQuery('#edit-cohort-date option:selected').val();
			var start_date = jQuery('#edit-cohort-date option:selected').text();
			if(value_date.length > 0 ){
				var GET_END_DATE = new Date(value_date);
				var END_DATE = GET_END_DATE.setMonth(GET_END_DATE.getMonth() +  5);
				END_DATE = new Date(END_DATE);
	
				var PROGRAM_END_DATE = (END_DATE.toLocaleString('en-us', { month: 'short' })) + ' '+
				(END_DATE.getDate() < 10 ? '0' : '') + END_DATE.getDate()+', '+
				END_DATE.getFullYear();
				var FEE = jQuery('input[name=x_amount_total]').val()+' '+jQuery('input[name=x_currency_code]').val();
				jQuery('span#startdate').text(start_date);
				jQuery('span#enddate').text(PROGRAM_END_DATE);

				jQuery('.srl-payeezy-payment-form input[name=start_date]').val(value_date);
				jQuery('#edit-program-start-date').val(start_date);
				jQuery('#edit-expected-graduation-date').val(PROGRAM_END_DATE);
			}else{
				jQuery('.srl-payeezy-payment-form input[name=start_date]').val(0);
			}
		// var termofuse = jQuery('.srl-payeezy-payment-form input[name=term_of_use]').val();
		// var enrollment_agreement = jQuery('.srl-payeezy-payment-form input[name=enrollment_agreement]').val();
		// if(termofuse == 'Y' && enrollment_agreement == 'Y'){
		// 	jQuery('#errormodal .modal-header button').click();
		// }
	});
	jQuery(".mbscroller-init").mCustomScrollbar({
		theme:"dark",
		autoExpandScrollbar:true,
		axis:"y",
		advanced:{autoExpandHorizontalScroll:true},
		callbacks:{
			onTotalScrollOffset: 150,
			onTotalScroll:function(){
				//console.log("Scrolled to end of content.");
				jQuery('#tou-btn-section').removeClass('terms-btn-container');
				jQuery('.srl-payeezy-payment-form #accept-agreements').css('pointer-events','auto');
			},
			onScroll:function(){
				// jQuery('.srl-payeezy-payment-form #accept-agreements').css('pointer-events','none');
			},
			onCreate:function(){
				//console.log("Plugin markup generated");
			},
			onInit:function(){
				// jQuery('.srl-payeezy-payment-form button#accept-agreements').css('pointer-events','none');
			}
		}
	});
	jQuery(".enrolment_term_agreement").mCustomScrollbar({
		theme:"dark",
		autoExpandScrollbar:true,
		axis:"y",
		advanced:{autoExpandHorizontalScroll:true},
		callbacks:{
			onTotalScrollOffset: 150,
			onTotalScroll:function(){
				//console.log("Scrolled to end of content.");
				jQuery('.srl-payeezy-payment-form a#accept-agreement').css('pointer-events','auto');
			},
			onScroll:function(){
			//   jQuery('.srl-payeezy-payment-form button#accept-agreement').css('pointer-events','none');
			},
			onCreate:function(){
				//console.log("Plugin markup generated");
			},
			onInit:function(){
				// jQuery('.srl-payeezy-payment-form button#accept-agreement').css('pointer-events','none');
			}
		}
	});
	jQuery('#planModalbox').on('hidden.bs.modal', function () {
		var URL = jQuery('#planModalbox').data('url');
		document.location.href= URL;
	});
	if (window.outerWidth > 768){
		if (!!document.getElementById('online-payment-container')){

			jQuery(window).resize(function () {
				var payment_height = new Array();
				payment_height.push(jQuery(".upfront_payment .item-detail").height());
				payment_height.push(jQuery(".finance_meritize .item-detail").height());
				payment_height.push(jQuery(".climb_credit_loan .item-detail").height());
				payment_height.push(jQuery(".finance_climb_credit_loan .item-detail").height());
				var payment_section_height = Math.max.apply(Math, payment_height); // 3
				//jQuery('#online-payment-container .paymentoptions').css('height', (payment_section_height + 50) +"px");
				
			});
		}
	}
});
jQuery(window).on('load', function () {
	if (window.outerWidth > 768) {
		if (!!document.getElementById('online-payment-container')) {

			var payment_height = new Array();
			payment_height.push(jQuery(".upfront_payment .item-detail").height());
			payment_height.push(jQuery(".finance_meritize .item-detail").height());
			payment_height.push(jQuery(".climb_credit_loan .item-detail").height());
			payment_height.push(jQuery(".finance_climb_credit_loan .item-detail").height());
			var payment_section_height = Math.max.apply(Math, payment_height); // 3
			//jQuery('#online-payment-container .paymentoptions').css('height', (payment_section_height + 50) + "px");
		}
	}
});

(function($, Drupal, drupalSettings) {
	Drupal.behaviors.srl_payment = {
	  attach(context, settings) {
		$.fn.createHubspotTaskPopup = function(data) {
			// $('#planModalbox .modal-footer').html('<a href"'+data['pdf_url']+'">Download agreement</a>');
			$("#planModalbox").modal('show');
		//   console.log('custom submit ajax form');
		};
		$.fn.dateValidation = function(data) {
			console.log('date validation');
			// jQuery("#edit-finance-options option[value='SFO']").attr("selected", true);
			jQuery("#edit-finance-options").val('SFO').change();
			if(jQuery('#edit-cohort-date').hasClass('form-select')){
				var value_date = jQuery('#edit-cohort-date option:selected').val();
			}else{
				var value_date = 'field not found';
			}
			if (value_date.length == 0) {
				jQuery('#edit-cohort-date').addClass('error');
				jQuery('#edit-cohort-date').focus();
				jQuery('#class-date-form .date-select-error').text('Please select class start date.');
			}
		};
		$.fn.agreementValidation = function(data){
			console.log('instalment calld');
			// jQuery("#edit-finance-options option[value='SFO']").attr("selected", true);
			jQuery("#edit-finance-options").val('SFO').change();
			if(jQuery('#edit-cohort-date').hasClass('form-select')){
				var value_date = jQuery('#edit-cohort-date option:selected').val();
			}else{
				var value_date = 'field not found';
			}
			if (value_date.length == 0) {
				jQuery('#edit-cohort-date').addClass('error');
				jQuery('#edit-cohort-date').focus();
				jQuery('#class-date-form .date-select-error').text('Please select class start date.');
				// alert('value not selected');
			}else{
				jQuery('#edit-cohort-date').removeClass('error');
				jQuery('#class-date-form .date-select-error').text('');
				var termofuse = jQuery('.srl-payeezy-payment-form input[name=term_of_use]').val();
				var enrollment_agreement = jQuery('.srl-payeezy-payment-form input[name=enrollment_agreement]').val();
				if(termofuse == 'Y' && enrollment_agreement == 'Y'){
					// jQuery('.srl-payeezy-payment-form .card-button.button.form-submit').show();
					jQuery('.srl-payeezy-payment-form .button.pay-submit-button').hide();
					jQuery('#errormodal .modal-header button').click();
				}else{
					// jQuery('.srl-payeezy-payment-form .card-button.button.form-submit').hide();
					jQuery('.srl-payeezy-payment-form .button.pay-submit-button').show();
					jQuery('.srl-payeezy-payment-form .enrolment_agreement_error_message').show();
					jQuery('.srl-payeezy-payment-form a.error-popup-button').click();
				}
			}
		};
		$.fn.payzeepayment = function(data){
			var openWin = window.open("" ,"_self", config='height=screen.height, width=screen.width,screenX=0,screenY=0 top=0,left=0,menubar=no, scrollbars=yes, resizable=yes,directories=0,titlebar=0,toolbar=0,location=0, status=no,addressbar=0');
			openWin.moveTo( 0, 0 );
			openWin.resizeTo( screen.availWidth, screen.availHeight);
			document.getElementById("srl-payeezy-payment-form").method="post";	
			document.getElementById("srl-payeezy-payment-form").action= data['payment_redirection'];
			document.getElementById("srl-payeezy-payment-form").target = "_self";
			//document.getElementById("nsRqst").target = "_self";	
			document.getElementById("srl-payeezy-payment-form").submit();
		};
		$.fn.agreementfieldValidation = function (data){
			$('.srl-payeezy-payment-form input[name=enrollment_agreement]').val('Y');
			$('.enrollment-agreement .agreement-validate').show();
			$('.enrollment-agreement span.msg-agreement').hide();
			$("#myModal").modal('hide');
		};
		$.fn.agreementScroll = function(data){
			setTimeout(function() {
				// jQuery(".enrolment_term_agreement").mCustomScrollbar('scrollTo',['top',-150]);
			},250);
		};
		$.fn.paymentredirectloader = function(payment_link){
			jQuery('.payment-proceed-loader').removeClass('d-none');
		};
		$.fn.paymentredirect = function(payment_link){
			jQuery('.payment-proceed-loader').removeClass('d-none');
			setTimeout(function () {
				jQuery('.payment-proceed-loader').addClass('d-none');
				var openWin = window.open(payment_link.link ,payment_link.window_name, config='height=screen.height, width=screen.width,screenX=0,screenY=0 top=0,left=0,menubar=no, scrollbars=yes, resizable=yes,directories=0,titlebar=0,toolbar=0,location=0, status=no,addressbar=0');
				// openWin.moveTo( 0, 0 );
				openWin.focus();
				openWin.resizeTo( (screen.availWidth/2), (screen.availHeight/2)+200);
			}, 8000);
			lastAccessObj = new Object();
			lastAccessObj.uid = 0;
			lastAccessObj.ev = 'payment';
			lastAccessObj.evtyp = "order confirm";
			lastAccessObj.evVal = payment_link.btn_name;
			lastAccessObj.domain = drupalSettings.site_information.Domain;;
			lastAccessObj.sitetype = payment_link.sitetype;
			lastAccessObj.sitename = drupalSettings.site_information.siteName;
			
			lastAccessObj.page = 'Order summary';
			lastAccessObj.pgurl = window.location.href;
			lastAccessObj.program = payment_link.program_name;
			lastAccessObj.bdate = payment_link.batch_date;
			lastAccessObj.ut_md = drupalSettings.utm_medium;
			lastAccessObj.ut_src = drupalSettings.utm_source;
			lastAccessObj.ut_cmpn = drupalSettings.utm_campaign;
			lastAccessObj.ut_trm = drupalSettings.utm_term;
			lastAccessObj.ut_cnt = drupalSettings.utm_content;
			lastAccessObj.gclid = 'CjwKCAjwqcKFBhAhEiwAfEr7zdaL4mFYvlRJ2n3rxfWX9sceY6lzoSm6hNlngYS8nWS9iqqWCbU11hoC0GwQAvD_BwEs';
			lastAccessObj.trn_id = payment_link.transaction_id;
			lastAccessObj.lstatus = 'High touch modal';
			lastAccessObj.jtype = 'App completed';
			sp.track('payment', lastAccessObj);
		};
		$.fn.onlinepaymentredirect = function(payment_link){
			jQuery('.payment-proceed-loader').removeClass('d-none');
			setTimeout(function () {
				jQuery('.payment-proceed-loader').addClass('d-none');
				var openWin = window.open(payment_link.link ,payment_link.window_name, config='height=screen.height, width=screen.width,screenX=0,screenY=0 top=0,left=0,menubar=no, scrollbars=yes, resizable=yes,directories=0,titlebar=0,toolbar=0,location=0, status=no,addressbar=0');
				// openWin.moveTo( 0, 0 );
				openWin.focus();
				openWin.resizeTo( (screen.availWidth/2), (screen.availHeight/2)+200);
			}, 8000);
			lastAccessObj = new Object();
			lastAccessObj.uid = 0;
			lastAccessObj.ev = 'online payment';
			lastAccessObj.evtyp = "online order confirm";
			lastAccessObj.evVal = payment_link.btn_name;
			lastAccessObj.domain = drupalSettings.site_information.Domain;
			lastAccessObj.sitetype = payment_link.sitetype;
			lastAccessObj.sitename = drupalSettings.site_information.siteName;
			
			lastAccessObj.page = 'online payment';
			lastAccessObj.pcourse = payment_link.payment_course;
			lastAccessObj.pgurl = window.location.href;
			lastAccessObj.ut_md = drupalSettings.utm_medium;
			lastAccessObj.ut_src = drupalSettings.utm_source;
			lastAccessObj.ut_cmpn = drupalSettings.utm_campaign;
			lastAccessObj.ut_trm = drupalSettings.utm_term;
			lastAccessObj.ut_cnt = drupalSettings.utm_content;
			lastAccessObj.gclid = 'CjwKCAjwqcKFBhAhEiwAfEr7zdaL4mFYvlRJ2n3rxfWX9sceY6lzoSm6hNlngYS8nWS9iqqWCbU11hoC0GwQAvD_BwEs';
			sp.track("online payment", lastAccessObj);
			console.log(payment_link);
		};
		$.fn.accepttermofusejscallback = function(policy_data){
			console.log('accept term of use');
			jQuery('.srl-payeezy-payment-form input[name=term_of_use]').val('Y');
			jQuery('.term-of-use .term-validate').show();
			jQuery('.term-of-use span.msg-agreement').hide();
			var termofuse = jQuery('.srl-payeezy-payment-form input[name=term_of_use]').val();
			var enrollment_agreement = jQuery('.srl-payeezy-payment-form input[name=enrollment_agreement]').val();
			if(termofuse == 'Y' && enrollment_agreement == 'Y'){
				if(jQuery('#edit-cohort-date').hasClass('form-select')){
					var value_date = jQuery('#edit-cohort-date option:selected').val();
				}else{
					var value_date = 'field not found';
				}
				if(value_date.length > 0){
					jQuery('#errormodal .modal-header button').click();
				}
			}
			$('#myfirstmodal').modal('hide');
		};
		$.fn.rejecttermofusejsCallback = function(policy_data){
			jQuery('.srl-payeezy-payment-form input[name=term_of_use]').val('N');
			jQuery('.term-of-use .term-validate').hide();
			jQuery('.term-of-use span.msg-agreement').show();
			jQuery('.srl-payeezy-payment-form .enrolment_agreement_error_message').show();
			jQuery('.srl-payeezy-payment-form a.error-popup-button').click();
			jQuery('#term-accepted').removeClass('accepted-1');
			$('#myfirstmodal').modal('hide');
		};
		$.fn.rejectagreementjsCallback = function(policy_data){
			console.log('reject agreement');
			jQuery('.enrollment-agreement .agreement-validate').hide();
			jQuery('.enrollment-agreement span.msg-agreement').show();
			jQuery('.srl-payeezy-payment-form input[name=enrollment_agreement]').val('N');
			jQuery('.srl-payeezy-payment-form .enrolment_agreement_error_message').show();
			jQuery('.srl-payeezy-payment-form a.error-popup-button').click();
			jQuery('#agr-accepted').removeClass('accepted-1');
			$('#myModal').modal('hide');
		};
		$.fn.acceptagreement = function(policy_data){
			console.log('accept agreement');
			jQuery('.srl-payeezy-payment-form input[name=enrollment_agreement]').val('Y');
			var termofuse = jQuery('.srl-payeezy-payment-form input[name=term_of_use]').val();
			var enrollment_agreement = jQuery('.srl-payeezy-payment-form input[name=enrollment_agreement]').val();
			if(termofuse == 'Y' && enrollment_agreement == 'Y'){
				if(jQuery('#edit-cohort-date').hasClass('form-select')){
					var value_date = jQuery('#edit-cohort-date option:selected').val();
				}else{
					var value_date = 'field not found';
				}
				if(value_date.length > 0){
					jQuery('#errormodal .modal-header button').click();
				}
			}
			$('#myModal').modal('hide');
		};
		$.fn.errorinAPI = function(policy_data){
			console.log('error in API');
			jQuery('#apierror').modal('show');
		};
	  }
	};
  })(jQuery, Drupal, drupalSettings);

  function addMonths(date, months) {
    var d = date.getDate();
    date.setMonth(date.getMonth() + +months);
    if (date.getDate() != d) {
      date.setDate(0);
    }
    return date;
}
