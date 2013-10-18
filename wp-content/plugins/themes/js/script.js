jQuery(document).ready(function($){
	
	addClickEdit();
	
	$("#add_new_theme").click(function(e){
		e.preventDefault();
		$("#add_new_table").toggle();
	});
	
	$("#edit_theme_options").click(function(e){
		e.preventDefault();
		$("#edit_options_table").toggle();
	});
	
	function addClickEdit(){
		$(".theme_name_link").click(function(e){
			e.preventDefault();
			var id = $(this).attr("id");
			console.log(id);
			$(document).data("edit_id", id);
			$("#theme_"+id).children().load("/wp-content/plugins/themes/editForm.php?id="+id);
			$("#theme_"+id).show();
			$(".theme_name_link").unbind("click");
		});
	}

	$("#themes_new_form").validate({
		debug:true,
		rules: { 
			themes_name: {
				required: true
			},
			themes_thumb: {
				required: true
			}, 
			themes_zip: { 
				required: true, 
			}, 
			themes_version: { 
				required: true,
			},
		},
	
		errorPlacement: function(error, element) {
			error.insertAfter(element);
		}
	});
	
	$("#themes_new_form #themes_zip").change(function(){
        $("#themes_zip").blur().focus(); 
    });
	$("#themes_new_form #themes_thumb").change(function(){
        $("#themes_new_form #themes_thumb").blur().focus(); 
    });

	$("#themes_delete").live("click", function(e){
		e.preventDefault();
		var answer = confirm("Do you want to delete the theme?");

		if(answer){
			$("#theme_action").val("delete");
			$("#edit_form").submit();
		}
		else{
			return false;
		}
	});
	
	$("#themes_delete_cancel").live("click", function(e){
		//e.preventDefault();
		var id = $(document).data("edit_id");
		$("#theme_"+id).children().html("");
		$("#theme_"+id).hide();
		addClickEdit();				
	});
	
	
	
	
	
});
