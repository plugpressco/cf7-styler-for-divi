/**
 * CF7 Mate – Partial Save / Save & Continue
 * Saves form progress to a WP transient keyed by a localStorage token.
 */
( function () {
	'use strict';

	if ( typeof cf7mPartialSave === 'undefined' ) {
		return;
	}

	const { root, nonce, i18n } = cf7mPartialSave;

	function generateToken() {
		const arr = new Uint8Array( 16 );
		window.crypto.getRandomValues( arr );
		return Array.from( arr, ( b ) => b.toString( 16 ).padStart( 2, '0' ) ).join( '' );
	}

	function storageKey( formId ) {
		return 'cf7m_token_' + formId;
	}

	function getToken( formId ) {
		let token = localStorage.getItem( storageKey( formId ) );
		if ( ! token || token.length !== 32 ) {
			token = generateToken();
			localStorage.setItem( storageKey( formId ), token );
		}
		return token;
	}

	function setStatus( bar, msg, type ) {
		const el = bar.querySelector( '.cf7m-partial-save__status' );
		if ( ! el ) return;
		el.textContent = msg;
		el.className = 'cf7m-partial-save__status' + ( type ? ' is-' + type : '' );
		if ( type !== 'saving' ) {
			setTimeout( () => {
				el.textContent = '';
				el.className = 'cf7m-partial-save__status';
			}, 3000 );
		}
	}

	function collectFormData( form ) {
		const data = {};
		const elements = form.querySelectorAll(
			'input:not([type="submit"]):not([type="button"]):not([type="reset"]):not([type="file"]):not([name^="_"]), textarea, select'
		);
		elements.forEach( ( el ) => {
			if ( ! el.name ) return;
			const name = el.name.replace( /\[\]$/, '' );
			if ( el.type === 'checkbox' ) {
				if ( ! Array.isArray( data[ name ] ) ) data[ name ] = [];
				if ( el.checked ) data[ name ].push( el.value );
			} else if ( el.type === 'radio' ) {
				if ( ! Object.prototype.hasOwnProperty.call( data, name ) ) data[ name ] = '';
				if ( el.checked ) data[ name ] = el.value;
			} else if ( el.multiple ) {
				data[ name ] = Array.from( el.selectedOptions, option => option.value );
			} else {
				data[ name ] = el.value;
			}
		} );
		return data;
	}

	function restoreFormData( form, data ) {
		Object.entries( data ).forEach( ( [ name, value ] ) => {
			const field = CSS.escape( name.replace( /\[\]$/, '' ) );
			const els = form.querySelectorAll( '[name="' + field + '"], [name="' + field + '[]"]' );
			els.forEach( ( el ) => {
				if ( el.type === 'checkbox' ) {
					el.checked = [].concat( value ).includes( el.value );
				} else if ( el.type === 'radio' ) {
					el.checked = el.value === value;
				} else if ( el.multiple ) {
					Array.from( el.options ).forEach( option => { option.selected = [].concat( value ).includes( option.value ); } );
				} else {
					el.value = value;
				}
			} );
		} );
		// Evaluate dependent controls only after every restored value is in place.
		form.querySelectorAll( 'input,select,textarea' ).forEach( el => {
			if ( Object.prototype.hasOwnProperty.call( data, el.name.replace( /\[\]$/, '' ) ) ) el.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		} );
	}

	function injectTokenInput( form, formId, token ) {
		let input = form.querySelector( 'input[name="_cf7m_ps_token"]' );
		if ( ! input ) {
			input = document.createElement( 'input' );
			input.type = 'hidden';
			input.name = '_cf7m_ps_token';
			form.appendChild( input );
		}
		input.value = token;
	}

	async function saveProgress( formId, token, data, bar, silent ) {
		if ( ! silent ) setStatus( bar, i18n.saving, 'saving' );
		try {
			const res = await fetch( root + '/partial-save', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': nonce,
				},
				body: JSON.stringify( { form_id: parseInt( formId, 10 ), token, data } ),
			} );
			if ( res.ok && ! silent ) {
				setStatus( bar, i18n.saved, 'saved' );
			} else if ( ! res.ok && ! silent ) {
				setStatus( bar, i18n.error, 'error' );
			}
		} catch ( e ) {
			if ( ! silent ) setStatus( bar, i18n.error, 'error' );
		}
	}

	function debounce( fn, ms ) {
		let timer;
		const debounced = ( ...args ) => {
			clearTimeout( timer );
			timer = setTimeout( () => fn( ...args ), ms );
		};
		debounced.cancel = () => clearTimeout( timer );
		return debounced;
	}

	function initBar( bar ) {
		const formId = bar.dataset.formId;
		if ( ! formId ) return;

		const form = bar.closest( 'form.wpcf7-form' ) || bar.closest( '.wpcf7' )?.querySelector( 'form' );
		if ( ! form || bar.dataset.cf7mInitialized ) return;
		bar.dataset.cf7mInitialized = '1';

		let token = getToken( formId );
		injectTokenInput( form, formId, token );

		// Restore on load.
		fetch( root + '/partial-save/' + token, {
			headers: { 'X-WP-Nonce': nonce },
		} )
			.then( ( r ) => r.json() )
			.then( ( json ) => {
				if ( json.found && json.data && Object.keys( json.data ).length ) {
					restoreFormData( form, json.data );
					setStatus( bar, i18n.restored, 'saved' );
				}
			} )
			.catch( () => {} );

		// Save button click.
		const btn = bar.querySelector( '.cf7m-partial-save__btn' );
		if ( btn ) {
			btn.addEventListener( 'click', () => {
				saveProgress( formId, token, collectFormData( form ), bar, false );
			} );
		}

		// Auto-save on field change (debounced 3s, silent).
		const debouncedSave = debounce( () => {
			saveProgress( formId, token, collectFormData( form ), bar, true );
		}, 3000 );

		form.addEventListener( 'change', debouncedSave );
		form.addEventListener( 'input', debouncedSave );

		// Clear on successful submission.
		form.closest( '.wpcf7' )?.addEventListener( 'wpcf7mailsent', () => {
			debouncedSave.cancel();
			fetch( root + '/partial-save/' + token, {
				method: 'DELETE',
				headers: { 'X-WP-Nonce': nonce },
			} ).catch( () => {} );
			localStorage.removeItem( storageKey( formId ) );
			token = getToken( formId );
			injectTokenInput( form, formId, token );
		} );
	}

	function init() {
		document.querySelectorAll( '.cf7m-partial-save-bar' ).forEach( initBar );
	}
	if ( document.readyState === 'loading' ) document.addEventListener( 'DOMContentLoaded', init );
	else init();
} )();
