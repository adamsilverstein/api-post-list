
( function( $ ) {

	console.log( 'Starting up!' );

	/**
	 * A basic view to display the posts
	 */
	var SinglePostView = wp.Backbone.View.extend( {

		template: wp.template( 'single-post' ),

		render: function() {
			this.$el.html( this.template( this.model ) );
		}

	} );

	var PostsCollectionView = wp.Backbone.View.extend( {


	} );


	var setupApp = function( area ) {
			console.log( $( area ) );
			console.log( $( area ).data() );

			var $area = $( area ),
				data  = $area.data(),

			// Set up a new collection view to contain the posts.
			collectionView = new PostsCollectionView();

			// Get the posts from the api.
			var posts = new wp.api.collections.Posts();

			// Fetch the posts, returning a promise.
			var promise = posts.fetch( {
				'data': {
					'include': data.posts.ids,
					'_embed': true
				}
			} );

			// Continue when the fetch completes.
			promise.complete( function() {

				// Loop thru the posts, creating and adding views.
				_.each( posts.models, function( post ) {

					var singlePost = new SinglePostView( { 'model': post } );

					collectionView.views.add( singlePost )
				} );


				// Insert it into the DOM placeholder.
				var selector = '.api-post-list-container[data-plid="' + data.plid + '"]',
				$placeholder = $( selector );

				// Render the collectionView.
				collectionView.render();
				$placeholder.html( collectionView.el );
				collectionView.views.ready();

			} );




	}

	/**
	 * When everything is loaded, set up our app.
	 */
	$( document ).ready( function() {

		// Wait for the client to load.
		wp.api.loadPromise.done( function() {

			// Grab the shortcode generated areas.
			$shorcodeAreas = $( '.api-post-list-container' );

			// Loop thru each shortcode area.
			_.each( $shorcodeAreas, function( area ) {
				setupApp( area );
			} );
		} )


	} );


} )( jQuery );
