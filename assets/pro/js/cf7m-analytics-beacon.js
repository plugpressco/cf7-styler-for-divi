/* CF7 Mate – Analytics beacon. Fires once per form visible on the page. */
( function () {
	if ( typeof cf7mAnalytics === 'undefined' || ! cf7mAnalytics.url ) {
		return;
	}

	function ping( formId ) {
		var body = JSON.stringify( { form_id: parseInt( formId, 10 ) } );
		{
			fetch( cf7mAnalytics.url, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': cf7mAnalytics.nonce,
				},
				body: body,
				keepalive: true,
			} ).catch( function () {} );
		}
	}

	function init() {
		var seen = {};
		document.querySelectorAll( '.wpcf7' ).forEach( function ( el ) {
			var input = el.querySelector( '[name="_wpcf7"]' );
			if ( ! input ) return;
			var id = input.value;
			if ( id && ! seen[ id ] ) {
				seen[ id ] = true;
				ping( id );
			}
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
