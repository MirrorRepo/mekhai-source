require([
            "jquery",
            "mage/mage"
        ], function ($) {
        	$(document).ready(function(){
        		var selected = $( "#is_range option:selected" ).val();
	            if(selected == 0) {
	            	$('#zipcode').attr('disabled','disabled');
	            } else {
	            	$('#dest_zip_from').attr('disabled','disabled');
	            	$('#dest_zip_to').attr('disabled','disabled');
	            }
	            $('#is_range').change(function () {
	            	var selectedVal = $( "#is_range option:selected" ).val();
		            if(selectedVal == 0) {
		            	$('#dest_zip_from').removeAttr('disabled','disabled');
		            	$('#dest_zip_to').removeAttr('disabled','disabled');
		            	$('#zipcode').attr('disabled','disabled');
		            	$('#zipcode').val('');
		            } else {
		            	$('#zipcode').removeAttr('disabled','disabled');
		            	$('#dest_zip_from').attr('disabled','disabled');
		            	$('#dest_zip_to').attr('disabled','disabled');
		            	$('#dest_zip_from').val('');
		            	$('#dest_zip_to').val('');
		            }
	            })
        	})
        });
