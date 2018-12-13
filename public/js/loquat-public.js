
$ = null
// accesskey = null

config = {
  accessKey: null,
  services: {
    event: {
      host: 'events.loquat.quanrio.com',
      port: 0,
      endpoints: {
        default: 'events' 
      },
      accessKey: null
    },
    recommendation: {
      host: 'rc-engine.loquat.quanrio.com/api/v1/recommend',
      port: 0,
      endpoints: {
        default: 'products'
      },
      accessKey: null
    }
  }
}

session = {
  currentPage: {},
  uid: null
} 

window.onload = function() {
  $ = jQuery
  populateWidget()
  initialize()
}

function initialize() {
  console.log('After document ready')

  session.uid = window.localStorage.getItem('PRSN_ID')
  if(session.uid === null) {

    console.log('# New User Visiting')
    session.uid = uuidv4()
    window.localStorage.setItem('PRSN_ID', session.uid)
    // document.cookie = "LQT_UID="+ session.uid +"; domain=.loquat.quanrio.com;path=/ expires=31 Dec 2029 23:59:59 GMT"

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
  }

  function getUrl(service_name) {
    var service = config.services[service_name]
    var url = 'http://' + service.host + ( (service.port)?':'+service.port:'' ) + '/' + service.endpoints.default
    return url
  }

  function getHeaders() {
    return {'Authorization': 'Bearer ' + config.accessKey}
  }

  function populateWidget(name, id) {

    console.log('# Populating widget')

    var $container = $('.loquat_hotslot .container .listing')
    var $template = $container.find('.item.template')

    var source = $('.loquat_hotslot').attr('data-type')

    list = []

  // console.log(loquat_page)
  var query =  {
    page: loquat_pagetype,
  //  user_id: session.uid
}

  // @todo: move id to be set via backend
  if(loquat_pagetype == 'product')
    query.product_id = $('.product')[0].id.split('-')[1]

  $.ajax({
    url: getUrl('recommendation'),
    method: 'GET',
    data: query,
    headers: getHeaders(),
    success: function(data){
      console.log(data)

      data.forEach(function(item) {
        $item = $template.clone(true, true)
        $item.removeClass('template')

        var querystring = $.param({
          personide: loquat_pagetype + '_' + source,
        })

        if(item.url !== undefined) {
          item.url = item.url + ( item['url'].includes('?') ? '' : '?' ) + querystring
        }
        // item.url = item.url.replace('https://www.goto.com.pk/', 'localhost/store/?product=')

        $item.find('.loquat-product__picture').css('background-image', 'url('+item.image_url+')')
        $item.find('.loquat-product__name').text(item.name)
        $item.find('.loquat-product__link').attr('href', item.url)
        $item.find('.loquat-product__price').text('Rs. '+item.sale_price)
        $container.append($item)

        console.log(item)
      })

      addRail()
    },
    error: function(xhr, err) {
      console.log(err)
    }
  })
}

(function( $ ) {
	// 'use strict';

  console.log('# Loading loquat')

  dispatch = function(data) {

    var timestamp = new Date()
    timestamp = timestamp.toISOString()
    data = Object.assign(data, {eventTime: timestamp})
    
    if(data.entityType === 'user') {
      data = Object.assign(data, {entityId: session.uid})
    }

    if(data.event === 'view' || data.event === 'add-to-cart') {
      var query = getQueryParams()
      if(query.personide) {
        data = Object.assign(data, {
          meta: {
            category: query.personide.split(/_(.+)/)[0],
            sourceElement: query.personide.split(/_(.+)/)[1]
          }
        })
      }
    }

    console.log(data)
    
    $.ajax({
      url: getUrl('event'),
      method: 'POST',
      contentType: 'application/json',
      headers: getHeaders(),
      data: JSON.stringify(data),
      xhrFields: {
        withCredentials: true
      },
      crossDomain: true,
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

  $('document').ready(function() {


  })

  addRail = function() {
    var $rail = $('.rail')
    var $navigate = $('.rail-navigation')

    var item_width = $rail.children('.item').outerWidth(true)
    var frame_quantity = Math.floor($('.frame').outerWidth() / item_width)
    var items_quantity = 1
    items_quantity = frame_quantity
    
    var total_items = $rail.children('.item').not('.template').length

    console.log(item_width)
    var minLeft = -(total_items - frame_quantity)*item_width
    var maxLeft = 0

    console.log('frame_quantity', frame_quantity)
    console.log('total_items', total_items)
    console.log('minLeft', minLeft)
    //$rail.children('.item').css('width', $item_width)
    

    $navigate.click(function(e){
      e.stopImmediatePropagation()
      console.log('Gota move the rail')

      var direction = e.target.dataset.direction
      var newLeft

      switch(direction) {
        case 'left':
        newLeft = parseFloat($rail.css('left')) + item_width*items_quantity
        break;
        
        case 'right':
        newLeft = parseFloat($rail.css('left')) - item_width*items_quantity
        break;
      }

      console.log('newLeft', newLeft)

      console.log(minLeft)

      if(newLeft < minLeft)
        newLeft = minLeft
      if(newLeft > maxLeft)
        newLeft = maxLeft

      console.log(newLeft)
      $rail.css('left', newLeft)

    })
  }

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
    return v.toString(16)
  })
}

function getQueryParams() {
  var vars = [], hash;
  var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('#')[0].split('&')
  for(var i = 0; i < hashes.length; i++) {
    hash = hashes[i].split('=')
    vars[hash[0]] = hash[1]
  }

  return vars
}