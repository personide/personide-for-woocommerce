(function( $ ) {

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

  config = {
    api_url: 'http://localhost:9001'
  }

  config = {
    host: 'localhost',
    port: '9000',
    endpoint: '/events',
    accessKey: 'WPgcXKd42FPQpZHVbVeMyqF4CQJUnXQmIMTHhX3ZUrSzvy1KXJjdFUrslifa9rnB'
  }

  event_server_url = config.host + ':' + config.port + config.endpoint + config.accessKey

  lib = {

    newProduct: function(payload, callback) {
      console.log(payload)
      // payload = JSON.parse(payload)
      $.ajax({
        url: config.api_url + '/products',
        method: 'POST',
        data: payload,
        success: callback
      })
    },

    deleteProdcut: function(id, callback) {
      callback()
    },

    updateProduct: function(payload, callback) {

      var id = payload.id
      delete payload.id

      $.ajax({
        url: config.api_url + '/products' + '?' + 'id=' + id,
        method: 'PUT',
        data: payload,
        success: callback
      })
    }
  }

  dispatch = function(data) {

    var timestamp = new Date()
    timestamp = timestamp.toISOString()

    data = Object.assign(data, {eventTime: timestamp})
    console.log(data)
    // $.ajax({
    //   url: event_server_url,
    //   method: 'POST',
    //   data: data
    // })
  }

})( jQuery );
