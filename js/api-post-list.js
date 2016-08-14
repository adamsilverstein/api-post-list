
( function( $ ) {

	/**
	 * A basic view to display the posts
	 */
	var SinglePostView = wp.Backbone.View.extend( {

		// Set up our template function: wp.template returns a function.
		template: wp.template( 'single-post' ),

		/**
		 * Watch for events on the view.
		 */
		events: {
			'input .api-post-list-title': 'debouncedTitleInputHandler'
		},

		/**
		 * Handle input events for the title field.
		 *
		 * @param  Object e Event object.
		 */
		titleInput: function( e ) {
			this.model.set( 'title', jQuery( e.currentTarget ).text() );
			this.model.save();
		},

		/**
		 * A debounced version of the title change input handler.
		 *
		 * @param  Object e Event object.
		 */
		debouncedTitleInputHandler: _.debounce( function( e ) {
			this.titleInput( e );
			} , 2000 ),

		/**
		 * Render the single post view.
		 */
		render: function() {

			// Render this view by passing the model to the template function.
			this.$el.html( this.template( this.model ) );
		}
	} );

	/**
	 * PostsCollectionView is a container view that will contain the other views.
	 */
	var PostsCollectionView = wp.Backbone.View.extend( {} );

	/**
	 * Setup our app for a specific shortocde generated area.
	 *
	 * This function is called for each are visible on the page and
	 * ties the app to that area and its ids.
	 *
	 * @param  String area The selection target of the area to tie the app to.
	 */
	var setupApp = function( area ) {

		// Get the area data
		var $area = $( area ),
			data  = $area.data(),

		// Set up a new collection view to contain the posts.
		collectionView = new PostsCollectionView();

		// Get the posts from the api using the JS client.
		var posts = new wp.api.collections.Posts();

		// Fetch the posts, returning a promise.
		var promise = posts.fetch( {
			'data': {
				'include': data.posts.ids, // Include the passed ids
				'_embed': true // Embed all the post details including media.
			}
		} );

		// Continue when the fetch completes.
		promise.complete( function() {

			// Loop thru the posts, creating and adding views.
			_.each( posts.models, function( post ) {

				// Create a new view from the post.
				var singlePost = new SinglePostView( { 'model': post } );

				// Add the view to our container view.
				collectionView.views.add( singlePost )
			} );

			// Locate the placeholder for this instance.
			var selector = '.api-post-list-container[data-plid="' + data.plid + '"]',
			$placeholder = $( selector );

			// Render the collectionView.
			collectionView.render();

			// Insert the collectionView into the DOM.
			$placeholder.html( collectionView.el );
		} );
	}


	/**
	 * When the page is loaded, set up our app.
	 */
	$( document ).ready( function() {

		// Wait for the client to load.
		wp.api.loadPromise.done( function() {

			// Grab the shortcode generated areas.
			$shorcodeAreas = $( '.api-post-list-container' );

			// Loop thru each shortcode area.
			_.each( $shorcodeAreas, function( area ) {

				// Setup the app for this area.
				setupApp( area );
			} );
		} )
	} );


} )( jQuery );
