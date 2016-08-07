

console.log( layoutGeometry );


( function( $ ) {
	var container = $( 'api-image-gallery-container' );

	console.log( container );

	var media = new wp.api.collections.Media();

	media.fetch().done( function() {
		console.log( media.models );
		_.each( media.models( function( mediaModel ) {

		} ) );

	} );
} )( jQuery );
