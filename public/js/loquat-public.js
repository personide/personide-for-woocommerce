
$ = null

config = {
  host: 'events.loquat.quanrio.com',
  services: {
    event: {
      port: 80,
      endpoint: '/events',
      accessKey: 'WPgcXKd42FPQpZHVbVeMyqF4CQJUnXQmIMTHhX3ZUrSzvy1KXJjdFUrslifa9rnB'
    },
    recommendation: {
      port: 5000,
      endpoints: {
        default: 'recommended_for_you'
      }
    }
  }
}

window.onload = function() {
  $ = jQuery
  populateWidget()
}

function populateWidget(name, id) {

  console.log('# Populating widget')

  var recommendation = config.services.recommendation

  $container = $('.widget.loquat_recommendations')
  $template = $container.find('.item.template')

  list = []

  $.ajax({
    url: config.host + ':' + recommendation.port + '/' + recommendation.endpoints.default + '/?_id=d7ab6686-729c-4ef8-8f49-b4eedeef4629',
    method: 'GET',
    // data: {
    //   _id: '5b4f826ec0796b52766ab24d'
    // },
    success: function(data){
      console.log(data)

      data.forEach(function(item) {
        $item = $template.clone(true, true)
        $item.removeClass('template')

        // item.url = item.url.replace('https://www.goto.com.pk/', 'localhost/store/?product=')

        $item.find('.loquat-product__picture').css('background-image', 'url('+item.image_url+')')
        $item.find('.loquat-product__name').text(item.title)
        $item.find('.loquat-product__link').attr('href', item.url)
        $item.find('.loquat-product__price').text('Rs. '+item.sale_price)
        $container.append($item)
        console.log(item)
      })
    },
    error: function(xhr, err) {
      console.log(err)
    }
  })
}

(function( $ ) {
	// 'use strict';

  console.log('# Loading loquat')

  event_server_url = config.host + ':' + config.services.event.port + config.services.event.endpoint

  dispatch = function(data) {

    var timestamp = new Date()
    timestamp = timestamp.toISOString()
    data = Object.assign(data, {eventTime: timestamp})
    
    if(data.entityType === 'user') {
      data = Object.assign(data, {entityId: session.uid})
    }

    console.log(data)
    
    $.ajax({
      url: event_server_url,
      method: 'POST',
      data: data,
      success: function(res) {
        console.log(res)

        var history = window.localStorage.getItem('loquat_history')

        if (history) {
          history = JSON.parse(history)
        } else {
          history = []
        }

        history.push(data)
        history = JSON.stringify(history)
        window.localStorage.setItem('loquat_history', history)
      }
    })
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
        entityId: session.uid,
        properties: {
          id: session.uid
        }
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