( function( $ ) {

    wp.customize( 'boomerang_customizer[archive_layout]', function( value ) {
        value.bind( function( newval ) {
            $( '#boomerang-full' ).removeClass( 'horizontal vertical' );
            $( '#boomerang-full' ).addClass( newval );
        } );
    } );

    wp.customize( 'boomerang_customizer[primary_color]', function( value ) {
        value.bind( function( newval ) {
            document.documentElement.style.setProperty('--boomerang-primary-color', newval);
        } );
    } );

    wp.customize( 'boomerang_customizer[private_note_color]', function( value ) {
        value.bind( function( newval ) {
            document.documentElement.style.setProperty('--bommerang-team-color-color', newval);
        } );
    } );

} )( jQuery );