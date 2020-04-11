jQuery(document).ready(function($) {
	function memberscontrol_open_pointer(i) {
		pointer = membersPointers.pointers[i];
		options = $.extend( pointer.options, {
			close: function() {
				$.post( ajaxurl, {
					pointer: pointer.pointer_id,
					action: 'dismiss-wp-pointer'
				});
			}
		});
	
		$(pointer.target).pointer( options ).pointer('open');
	}
	memberscontrol_open_pointer(0);
});