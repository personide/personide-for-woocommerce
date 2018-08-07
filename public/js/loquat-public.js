(function( $ ) {
	'use strict';

  console.log('# Loading loquat')

  config = {
    host: 'localhost',
    port: '7070'
    endpoint: '/events.json?accessKey=',
    accessKey: 'WPgcXKd42FPQpZHVbVeMyqF4CQJUnXQmIMTHhX3ZUrSzvy1KXJjdFUrslifa9rnB'
  }

  event_server_url = config.host + ':' + config.port + config.endpoint + config.accessKey

  dispatch = {
    newUser = function(id, props) {
      var data = {
        event: '$set',
        entityType: 'user',
        entityId: id,
        properties: props,
        eventTime: new Date()
      }

      $.ajax({
        url: event_server_url,
        method: 'POST',
        data: data
      })
    },
  }

  var session = {
    currentPage: {},
    uid: null
  }

  $('document').ready(function() {
    session.uid = window.localStorage.getItem('LQT-UID')
    if(session.uid === null) {
    session.uid = '' // call generate
  }

  session.currentPage = getPageData()

  window.sessionStorage.setItem('lastPage', window.sessionStorage.getItem('currentPage'))
  window.sessionStorage.setItem('currentPage', JSON.stringify(session.currentPage))

  // console.log(session.currentPage)
  // console.log(JSON.parse(window.sessionStorage.getItem('lastPage')))

  if (session.currentPage.type == 'single-product') {
    // event: view
  }

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
