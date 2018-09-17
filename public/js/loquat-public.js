
$ = null

window.onload = function() {
  $ = jQuery
  populateWidget()
}

function populateWidget(name, id) {

  console.log('# Populating widget')

  $container = $('.widget.loquat_recommendations')
  $template = $container.find('.item.template')

  list = [1,2,3,4]

  list.forEach(function(item) {
    $item = $template.clone(true, true)
    $item.removeClass('template')
    $container.append($item)
  })
}

(function( $ ) {
	// 'use strict';

  console.log('# Loading loquat')

  config = {
    host: 'localhost',
    port: '9000',
    endpoint: '/events',
    accessKey: 'WPgcXKd42FPQpZHVbVeMyqF4CQJUnXQmIMTHhX3ZUrSzvy1KXJjdFUrslifa9rnB'
  }

  event_server_url = config.host + ':' + config.port + config.endpoint + config.accessKey

  dispatch_old = {
    newUser: function(id, props) {
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

  var session = {
    currentPage: {},
    uid: null
  }

  $('document').ready(function() {
    session.uid = window.localStorage.getItem('LQT-UID')
    if(session.uid === null) {
      session.uid = ''
    }

    

    session.currentPage = getPageData()

    window.sessionStorage.setItem('lastPage', window.sessionStorage.getItem('currentPage'))
    window.sessionStorage.setItem('currentPage', JSON.stringify(session.currentPage))

    console.log(session.currentPage)
    console.log(JSON.parse(window.sessionStorage.getItem('lastPage')))

  })

})( jQuery );


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