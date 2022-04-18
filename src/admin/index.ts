document.addEventListener( "click", function ( e ) {
	const target = e.target as HTMLElement;
	if ( target.classList.contains( "editinline" ) ) {
		const tr = target.closest( 'tr' );
		const id = tr.id;
		const checked = !!document.getElementById( id )?.querySelector( "[data-use-schedule]" );
		const checkbox = document.querySelector( '.inline-edit-row input[name=term-use_schedule]' ) as HTMLInputElement;
		checkbox.checked = checked;
	}
} );
