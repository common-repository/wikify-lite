jQuery( document ).ready(function($) {

	/* COMMENTS */
	$('#wikifyCommentsForm').on('submit', function(e) {
		e.preventDefault();

		var $form = $(this);
 
		$.post($form.attr('action'), $form.serialize(), function(data) {
			if (data.status == "success") {
				var commentHTML = data.html;
				$('#wikifyCommentFormContent').val("");
				$("#wikifyCommentsList").prepend($(commentHTML).animate({backgroundColor: '#ff0033'}, 1000).animate({backgroundColor: '#fff'}, 1000));
				
			}
		}, 'json');
	});
	
	
	$("#queryFilterBar :input").change(function() {
		this.form.submit();
	});	
	
});