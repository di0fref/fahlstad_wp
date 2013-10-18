jQuery(document).ready(function($){

	$("#contact_block input").focus(function(){
		$(this).addClass("yellow");
	});
	
	$("#contact_block input").blur(function(){
		$(this).removeClass("yellow");
	});

	$("#contact_form").validate({
		//debug:true,
		rules: { 
			_name: {
				required: true
			},
			email: {
				required: true, 
				email: true 
			}, 
			url: { 
				url: true 
			}, 
			message: { 
				required: true 
			},
		},
		errorPlacement: function(error, element) {
			error.insertAfter(element);
		}, 
	});
});