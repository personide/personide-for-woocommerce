
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
    
    if(data.entityType === 'user') {
      data = Object.assign(data, {entityId: session.uid})
    }

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

    session.uid = window.localStorage.getItem('LQT_UID')
    if(session.uid === null) {

      console.log('# New User Visiting')
      session.uid = uuidv4()
      window.localStorage.setItem('LQT_UID', session.uid)
      document.cookie = "LQT_UID="+ session.uid +"; expires=31 Dec 2029 23:59:59 GMT"

      dispatch({
        event: '$set',
        entityType: 'user',
        entityId: session.uid
      })

    }

    $('.add_to_cart_button.ajax_add_to_cart').click(function() {
      dispatch({
        event: 'add-to-cart',
        entityType: 'user',
        entityId: session.uid,
        targetEntityType: 'product',
        targetEntityId: $(this).data('product_id'),
        properties: {
          quantity: $(this).data('quantity')
        }
      })
    })

    $('.woocommerce-cart-form__cart-item .product-remove .remove').click(function() {
      dispatch({
        event: 'remove-from-cart',
        entityType: 'user',
        entityId: session.uid,
        targetEntityType: 'product',
        targetEntityId: $(this).data('product_id'),
        properties: null
      })
    })

    // window.sessionStorage.setItem('lastPage', window.sessionStorage.getItem('currentPage'))
    // window.sessionStorage.setItem('currentPage', JSON.stringify(session.currentPage))

    // console.log(session.currentPage)
    // console.log(JSON.parse(window.sessionStorage.getItem('lastPage')))

  })

  getPageData = function() {
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

function uuidv4() {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
    var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
    return v.toString(16);
  });
}