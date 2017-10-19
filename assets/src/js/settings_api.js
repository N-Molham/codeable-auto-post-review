/**
 * Created by Nabeel on 2016-02-02.
 */
(function ( $, win, undefined ) {
	$( function () {
		//Initiate Color Picker
		$( '.wp-color-picker-field' ).wpColorPicker();

		// Switches option sections
		var $groups = $( '.group' ).hide();

		// current open tab
		var active_tab = '';
		if ( 'undefined' !== typeof(localStorage) ) {
			active_tab = localStorage.getItem( 'mkecs_active_tab' );
		}

		// if url has section id as hash then set it as active or override the current local storage value
		if ( win.location.hash ) {
			active_tab = win.location.hash;
			if ( 'undefined' !== typeof(localStorage) ) {
				localStorage.setItem( 'mkecs_active_tab', active_tab );
			}
		}

		if ( active_tab !== '' && $( active_tab ).length ) {
			// open target tab
			$( active_tab ).fadeIn();
		} else {
			// open first tab's group
			$groups.first().fadeIn();
		}

		$groups.find( '.collapsed' ).each( function () {
			$( this ).find( 'input:checked' ).parent().parent().parent().nextAll().each( function () {
				if ( $( this ).hasClass( 'last' ) ) {
					$( this ).removeClass( 'hidden' );
					return false;
				}

				$( this ).filter( '.hidden' ).removeClass( 'hidden' );
			} );
		} );

		if ( '' !== active_tab && $( active_tab + '-tab' ).length ) {
			$( active_tab + '-tab' ).addClass( 'nav-tab-active' );
		} else {
			$( '.nav-tab-wrapper a:first' ).addClass( 'nav-tab-active' );
		}

		var $nav_links = $( '.nav-tab-wrapper a' );
		$nav_links.click( function ( e ) {
			e.preventDefault();

			$nav_links.removeClass( 'nav-tab-active' );

			var $this         = $( this ).addClass( 'nav-tab-active' ).blur(),
			    clicked_group = $this.attr( 'href' );

			if ( 'undefined' !== typeof(localStorage) ) {
				localStorage.setItem( "mkecs_active_tab", clicked_group );
			}

			$groups.hide();

			$( clicked_group ).fadeIn();
		} );

		$( '.mkecs-browse' ).on( 'click', function ( e ) {
			e.preventDefault();

			var $this = $( this );

			// Create the media frame.
			var file_frame = wp.media.frames.file_frame = wp.media( {
				title   : $this.data( 'uploader_title' ),
				button  : {
					text: $this.data( 'uploader_button_text' )
				},
				multiple: false
			} );

			file_frame.on( 'select', function () {
				var attachment = file_frame.state().get( 'selection' ).first().toJSON();
				$this.prev( '.mkecs-url' ).val( attachment.url ).change();
			} );

			// Finally, open the modal
			file_frame.open();
		} );
	} );
})( jQuery, window );