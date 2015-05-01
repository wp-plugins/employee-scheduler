jQuery(document).ready(function() {
 	jQuery('#thisdate').datetimepicker({
 		timepicker:false,
 		mask: true,
 		format:'Y-m-d'
 	});
 	jQuery('#thisdate2').datetimepicker({
 		timepicker:false,
 		mask: true,
 		format:'Y-m-d'
 	});
 	jQuery('#repeatuntil').datetimepicker({
 		timepicker:false,
 		mask: true,
 		format:'Y-m-d'
 	});
 	jQuery('#starttime').datetimepicker({
	  datepicker:false,
	  // mask: true,
	  format:'H:i',
	  step: 15,
	});
	jQuery('#endtime').datetimepicker({
	  datepicker:false,
	  // mask: true,
	  format:'H:i',
	  step: 15,
	});
	jQuery('#clockin').datetimepicker({
	  datepicker:false,
	  // mask: true,
	  format:'H:i',
	  step: 15,
	});
	jQuery('#clockout').datetimepicker({
	  datepicker:false,
	  // mask: true,
	  format:'H:i',
	  step: 15,
	});
	if(jQuery("#repeat").is(':checked'))
    	jQuery("#repeatfields").show();  // checked
	else
	    jQuery("#repeatfields").hide();  // unchecked
		jQuery('#repeat').onchange = function() {
	    jQuery('#repeatfields').style.display = this.checked ? 'block' : 'none';
	};

});



