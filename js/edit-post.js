( function() {

	/* ====== Tabs ====== */

	// Hides the tab content.
	jQuery( '.memberscontrol-tabs .memberscontrol-tab-content' ).hide();

	// Shows the first tab's content.
	jQuery( '.memberscontrol-tabs .memberscontrol-tab-content:first-child' ).show();

	// Makes the 'aria-selected' attribute true for the first tab nav item.
	jQuery( '.memberscontrol-tab-nav :first-child' ).attr( 'aria-selected', 'true' );

	// When a tab nav item is clicked.
	jQuery( '.memberscontrol-tab-nav li a' ).click(
		function( j ) {

			// Prevent the default browser action when a link is clicked.
			j.preventDefault();

			// Get the `href` attribute of the item.
			var href = jQuery( this ).attr( 'href' );

			// Hide all tab content.
			jQuery( this ).parents( '.memberscontrol-tabs' ).find( '.memberscontrol-tab-content' ).hide();

			// Find the tab content that matches the tab nav item and show it.
			jQuery( this ).parents( '.memberscontrol-tabs' ).find( href ).show();

			// Set the `aria-selected` attribute to false for all tab nav items.
			jQuery( this ).parents( '.memberscontrol-tabs' ).find( '.memberscontrol-tab-title' ).attr( 'aria-selected', 'false' );

			// Set the `aria-selected` attribute to true for this tab nav item.
			jQuery( this ).parent().attr( 'aria-selected', 'true' );
		}
	); // click()

}() );
