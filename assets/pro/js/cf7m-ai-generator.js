/**
 * CF7 Mate — AI Form Generator modal.
 *
 * Two states: INPUT (textarea + presets + optional image) → RESULT (editable
 * code + insert/copy). One single-column layout, no nested cards, all server
 * logic via the existing /cf7-styler/v1/ai-generate REST endpoint.
 *
 * @package CF7_Mate
 * @since 3.0.5
 */
( function ( $ ) {
	'use strict';

	var cfg     = window.cf7mAI || {};
	var strings = cfg.strings || {};
	var presets = cfg.presets || {};

	var $modal       = null;
	var $promptInput = null;
	var $codeInput   = null;
	var $imageInput  = null;
	var imageData    = null; // { base64, mime }

	function esc( s ) {
		var d = document.createElement( 'div' );
		d.textContent = s == null ? '' : String( s );
		return d.innerHTML;
	}

	// ── markup ──────────────────────────────────────────────────────────
	function buildModal() {
		var presetItems = Object.keys( presets ).map( function ( key ) {
			var p = presets[ key ] || {};
			return '<button type="button" class="cf7m-ai-preset" data-prompt="'
				+ esc( p.prompt || '' )
				+ '">'
				+ esc( p.name || key )
				+ '</button>';
		} ).join( '' );

		var providerBadge = cfg.hasApiKey
			? '<span class="cf7m-ai-provider">'
				+ esc( cfg.provider || 'AI' )
				+ ( cfg.model ? ' · ' + esc( cfg.model ) : '' )
				+ ' <a href="' + esc( cfg.settingsUrl || '#' ) + '" target="_blank" rel="noopener">'
				+ esc( strings.change || 'Change' )
				+ '</a></span>'
			: '<a class="cf7m-ai-provider cf7m-ai-provider--missing" href="'
				+ esc( cfg.settingsUrl || '#' )
				+ '" target="_blank" rel="noopener">'
				+ esc( strings.configure || 'Configure AI provider' )
				+ '</a>';

		var html = [
			'<div class="cf7m-ai-overlay" id="cf7m-ai-modal" role="dialog" aria-modal="true" aria-labelledby="cf7m-ai-title">',
			'  <div class="cf7m-ai-modal">',
			'    <header class="cf7m-ai-head">',
			'      <h2 id="cf7m-ai-title" class="cf7m-ai-title">' + esc( strings.title || 'AI Form Generator' ) + '</h2>',
			'      ' + providerBadge,
			'      <button type="button" class="cf7m-ai-close" aria-label="Close">&times;</button>',
			'    </header>',
			// Input view ───────────────────────────────────
			'    <section class="cf7m-ai-view cf7m-ai-view--input" data-view="input">',
			'      <label for="cf7m-ai-prompt" class="screen-reader-text">' + esc( strings.custom || 'Describe your form' ) + '</label>',
			'      <textarea id="cf7m-ai-prompt" class="cf7m-ai-textarea" rows="4" placeholder="'
				+ esc( strings.placeholder || 'Describe the form you want…' )
				+ '"></textarea>',
			'      <div class="cf7m-ai-image" id="cf7m-ai-image-wrap" hidden>',
			'        <img class="cf7m-ai-image__thumb" id="cf7m-ai-image-thumb" alt="">',
			'        <button type="button" class="cf7m-ai-image__clear" id="cf7m-ai-image-clear" aria-label="'
				+ esc( strings.removeImage || 'Remove image' )
				+ '">&times;</button>',
			'      </div>',
			'      <p class="cf7m-ai-error" id="cf7m-ai-error" role="alert" hidden></p>',
			'      <div class="cf7m-ai-actions">',
			'        <label class="cf7m-ai-attach">',
			'          <input type="file" id="cf7m-ai-image-input" accept="image/jpeg,image/png,image/gif,image/webp" hidden>',
			'          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 17.93 8.83l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>',
			'          ' + esc( strings.dropImage || 'Attach image' ),
			'        </label>',
			'        <span class="cf7m-ai-shortcut">' + esc( strings.shortcut || '⌘ + Enter' ) + '</span>',
			'        <button type="button" class="cf7m-ai-btn cf7m-ai-btn--primary" id="cf7m-ai-generate">',
			'          <span class="cf7m-ai-btn__label">' + esc( strings.generate || 'Generate' ) + '</span>',
			'        </button>',
			'      </div>',
			presetItems
				? '      <div class="cf7m-ai-presets"><span class="cf7m-ai-presets__label">'
					+ esc( strings.presets || 'Quick presets' )
					+ '</span><div class="cf7m-ai-presets__list">' + presetItems + '</div></div>'
				: '',
			'    </section>',
			// Result view ───────────────────────────────────
			'    <section class="cf7m-ai-view cf7m-ai-view--result" data-view="result" hidden>',
			'      <label for="cf7m-ai-code" class="screen-reader-text">Form code</label>',
			'      <textarea id="cf7m-ai-code" class="cf7m-ai-code" rows="14" spellcheck="false"></textarea>',
			'      <div class="cf7m-ai-actions">',
			'        <button type="button" class="cf7m-ai-btn cf7m-ai-btn--ghost" id="cf7m-ai-back">&larr; ' + esc( strings.regenerate || 'New prompt' ) + '</button>',
			'        <span class="cf7m-ai-spacer"></span>',
			'        <button type="button" class="cf7m-ai-btn cf7m-ai-btn--ghost" id="cf7m-ai-copy">' + esc( strings.copy || 'Copy' ) + '</button>',
			'        <button type="button" class="cf7m-ai-btn cf7m-ai-btn--primary" id="cf7m-ai-insert">' + esc( strings.insert || 'Insert' ) + '</button>',
			'      </div>',
			'    </section>',
			'  </div>',
			'</div>'
		].join( '\n' );

		$( 'body' ).append( html );
		$modal       = $( '#cf7m-ai-modal' );
		$promptInput = $( '#cf7m-ai-prompt' );
		$codeInput   = $( '#cf7m-ai-code' );
		$imageInput  = $( '#cf7m-ai-image-input' );

		bind();
	}

	// ── bindings ────────────────────────────────────────────────────────
	function bind() {
		$modal.on( 'click', function ( e ) {
			if ( e.target === $modal[ 0 ] ) close();
		} );
		$modal.find( '.cf7m-ai-close' ).on( 'click', close );

		$( document ).on( 'keydown.cf7mAI', function ( e ) {
			if ( e.key === 'Escape' && $modal.hasClass( 'is-open' ) ) close();
		} );

		// Presets
		$modal.on( 'click', '.cf7m-ai-preset', function () {
			var prompt = $( this ).data( 'prompt' );
			if ( ! prompt ) return;
			$promptInput.val( prompt );
			generate();
		} );

		// Generate
		$( '#cf7m-ai-generate' ).on( 'click', generate );
		$promptInput.on( 'keydown', function ( e ) {
			if ( e.key === 'Enter' && ( e.ctrlKey || e.metaKey ) ) generate();
		} );

		// Image
		$( '.cf7m-ai-attach' ).on( 'click', function ( e ) {
			// Let the label handle it natively
		} );
		$imageInput.on( 'change', function () {
			var file = this.files && this.files[ 0 ];
			if ( file ) readImage( file );
		} );
		$( '#cf7m-ai-image-clear' ).on( 'click', clearImage );

		// Drag-drop into the prompt area
		var $promptZone = $promptInput;
		$promptZone.on( 'dragover', function ( e ) {
			e.preventDefault();
			$promptZone.addClass( 'is-dragover' );
		} );
		$promptZone.on( 'dragleave dragend drop', function () {
			$promptZone.removeClass( 'is-dragover' );
		} );
		$promptZone.on( 'drop', function ( e ) {
			e.preventDefault();
			var dt = e.originalEvent.dataTransfer;
			if ( dt && dt.files && dt.files[ 0 ] ) readImage( dt.files[ 0 ] );
		} );

		// Result actions
		$( '#cf7m-ai-back' ).on( 'click', function () { switchView( 'input' ); } );
		$( '#cf7m-ai-copy' ).on( 'click', copy );
		$( '#cf7m-ai-insert' ).on( 'click', insert );
	}

	// ── views ───────────────────────────────────────────────────────────
	function switchView( view ) {
		$modal.find( '[data-view]' ).each( function () {
			var $v = $( this );
			$v.prop( 'hidden', $v.data( 'view' ) !== view );
		} );
		setTimeout( function () {
			if ( view === 'input' ) $promptInput.trigger( 'focus' );
			if ( view === 'result' ) $codeInput.trigger( 'focus' );
		}, 30 );
	}

	function open() {
		if ( ! $modal ) buildModal();
		$modal.addClass( 'is-open' );
		switchView( 'input' );
	}

	function close() {
		if ( $modal ) $modal.removeClass( 'is-open' );
	}

	// ── image ───────────────────────────────────────────────────────────
	function readImage( file ) {
		var reader = new FileReader();
		reader.onload = function () {
			var dataUrl = reader.result;
			var comma   = dataUrl.indexOf( ',' );
			imageData = {
				base64: dataUrl.slice( comma + 1 ),
				mime:   file.type || 'image/jpeg'
			};
			$( '#cf7m-ai-image-thumb' ).attr( 'src', dataUrl );
			$( '#cf7m-ai-image-wrap' ).prop( 'hidden', false );
		};
		reader.readAsDataURL( file );
	}

	function clearImage() {
		imageData = null;
		$imageInput.val( '' );
		$( '#cf7m-ai-image-thumb' ).attr( 'src', '' );
		$( '#cf7m-ai-image-wrap' ).prop( 'hidden', true );
	}

	// ── generate ────────────────────────────────────────────────────────
	function generate() {
		var prompt = ( $promptInput.val() || '' ).trim();

		if ( ! prompt && ! imageData ) {
			showError( strings.emptyError || 'Please describe the form, pick a preset, or attach an image.' );
			return;
		}
		if ( ! cfg.hasApiKey ) {
			showError( strings.noKey || 'Configure AI provider first.' );
			return;
		}

		hideError();

		var $btn = $( '#cf7m-ai-generate' );
		var orig = $btn.find( '.cf7m-ai-btn__label' ).text();
		$btn.prop( 'disabled', true );
		$btn.find( '.cf7m-ai-btn__label' ).html(
			'<span class="cf7m-ai-spinner" aria-hidden="true"></span>'
			+ ( strings.generating || 'Generating…' )
		);
		$modal.find( '.cf7m-ai-preset' ).prop( 'disabled', true );

		var payload = { prompt: prompt };
		if ( imageData ) {
			payload.prompt     = prompt || 'Convert this form design or screenshot into valid Contact Form 7 form code. Output only the form code.';
			payload.image      = imageData.base64;
			payload.image_type = imageData.mime;
		}

		$.ajax( {
			url:         cfg.generateUrl,
			method:      'POST',
			headers:     { 'X-WP-Nonce': cfg.nonce },
			contentType: 'application/json',
			data:        JSON.stringify( payload )
		} ).done( function ( r ) {
			if ( r && r.success && r.form ) {
				$codeInput.val( r.form );
				switchView( 'result' );
			} else {
				showError( ( r && r.message ) || strings.error );
			}
		} ).fail( function ( xhr ) {
			var msg = xhr.responseJSON && xhr.responseJSON.message;
			showError( msg || strings.error || 'Error generating form.' );
		} ).always( function () {
			$btn.prop( 'disabled', false );
			$btn.find( '.cf7m-ai-btn__label' ).text( orig );
			$modal.find( '.cf7m-ai-preset' ).prop( 'disabled', false );
		} );
	}

	// ── result actions ──────────────────────────────────────────────────
	function copy() {
		var code = $codeInput.val();
		if ( ! code ) return;
		var done = function () {
			var $b = $( '#cf7m-ai-copy' );
			var t  = $b.text();
			$b.text( strings.copied || 'Copied!' );
			setTimeout( function () { $b.text( t ); }, 1400 );
		};
		if ( navigator.clipboard && navigator.clipboard.writeText ) {
			navigator.clipboard.writeText( code ).then( done );
		} else {
			$codeInput.trigger( 'select' );
			document.execCommand( 'copy' );
			done();
		}
	}

	function insert() {
		var code = $codeInput.val();
		var $ta  = $( '#wpcf7-form' );
		if ( ! $ta.length ) {
			showError( strings.noEditor || 'Form editor not found.' );
			return;
		}
		$ta.val( code ).trigger( 'change' );
		close();
		$( 'html, body' ).animate( { scrollTop: $ta.offset().top - 80 }, 200 );
	}

	function showError( msg ) {
		$( '#cf7m-ai-error' ).text( msg ).prop( 'hidden', false );
	}
	function hideError() {
		$( '#cf7m-ai-error' ).text( '' ).prop( 'hidden', true );
	}

	// ── init ────────────────────────────────────────────────────────────
	$( document ).ready( function () {
		$( document ).on( 'click', '#cf7m-ai-btn', function ( e ) {
			e.preventDefault();
			open();
		} );
	} );
} )( jQuery );
