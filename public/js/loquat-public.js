(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
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

console.log('# Loading loquat')

var session = {
  currentPage: {}
}

$('document').ready(function() {
  session.currentPage = getPageData()

  window.sessionStorage.setItem('lastPage', window.sessionStorage.getItem('currentPage'))
  window.sessionStorage.setItem('currentPage', JSON.stringify(session.currentPage))

  console.log(session.currentPage)
  console.log(JSON.parse(window.sessionStorage.getItem('lastPage')))

})

function getPageData() {
  var type

  if( $('body').hasClass('single-product') ) {
    type = 'single-product'
  }

  else {
    type = ''
  }

  return {
    url: window.location.pathname + window.location.search,
    type: type,
    timestamp: new Date()
  }
}

})( jQuery );
