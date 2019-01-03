
Personide = {}

window.onload = function() {
  Personide.init()
  Personide.populateWidget()
};

////////////////////////////////////////////////
////////////////////////////////////////////////

(function( $ ) {

  var config = {
    accessKey: null,
    state: {},
    services: {
      event: {
        host: 'connect.personide.com',
        port: 0,
        endpoints: {
          default: 'events'
        },
        accessKey: null
      },
      recommendation: {
        host: 'connect.personide.com/api/v1',
        port: 0,
        endpoints: {
          default: 'products'
        },
        accessKey: null
      }
    }
  }

  // Initialize page session
  var storage = JSON.parse(window.sessionStorage.getItem('PERSONIDE'))
  if(!storage) {
    storage = {
      events: []
    }
    window.sessionStorage.setItem('PERSONIDE', JSON.stringify(storage))
  }

  var session = {
    currentPage: {},
    uid: null
  }

  Personide.setKey = function(key) {
    config.accessKey = String(key)
  }

  Personide.getKey = function() {
    return config.accessKey
  }

  Personide.set = function(key, value) {
    config.state[key] = value
  }

  /**
  ** Bind event triggers to DOM events
  **/

  Personide.init = function() {

    $('.add_to_cart_button.ajax_add_to_cart').click(function() {
      Personide.dispatch({
        event: 'add-to-cart',
        entityType: 'user',
        entityId: '',
        targetEntityType: 'item',
        targetEntityId: String($(this).data('product_id')),
        properties: {
          quantity: $(this).data('quantity')
        }
      })
    })

    $('.woocommerce-cart-form__cart-item .product-remove .remove').click(function() {
      Personide.dispatch({
        event: 'remove-from-cart',
        entityType: 'user',
        entityId: '',
        targetEntityType: 'item',
        targetEntityId: $(this).data('product_id'),
        properties: null
      })
    })

  }



  /**
  ** populate a widget skeleton with products returned from personide connect
  **/

  Personide.populateWidget = function(name, id) {

    if(config.state.currentPage === 'other') {
      return
    }

    var $container = $('.personide_container .listing')

    if($container.length == 0) {
      console.log('Personide container not found')
      return
    }

    console.log('# Populating widget')
    var $template = $container.find('.item.template')

    var source = $('.personide_container').attr('data-type')

    list = []

    var query =  {
      page: config.state.currentPage
    }

    console.log(config.state.currentPage)

    // @todo: move id to be set via backend
    if(config.state.currentPage == 'product')
      query.product_id = $('.product')[0].id.split('-')[1]

    $.ajax({
      url: getUrl('recommendation'),
      method: 'GET',
      data: query,
      headers: getHeaders(),
      xhrFields: {
        withCredentials: true
      },
      crossDomain: true,
      success: function(data) {
        console.log(data)

        data.forEach(function(item) {
          $item = $template.clone(true, true)
          $item.removeClass('template')

          var querystring = $.param({
            personide: config.state.currentPage + '_' + source,
          })

          if(item.url !== undefined) {
            item.url = item.url + ( item['url'].includes('?') ? '' : '?' ) + querystring
          }

          $item.find('.personide-product__picture').attr('src', item.image_url)
          $item.find('.personide-product__name').text(item.title)
          $item.find('.personide-product__link').attr('href', item.url)
          $item.find('.personide-product__price').text('Rs. '+item.sale_price)
          $container.append($item)
        })

        $('<link/>', {
          rel: 'stylesheet',
          type: 'text/css',
          href: 'http://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css'
        }).appendTo('head');

        $.getScript('http://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', function() {
          $('.personide_container .listing').slick({
            speed: 300,
            slidesToShow: 5,
            slidesToScroll: 3,
            nextArrow: '<img class="slick-arrow slick-next" src="'+config.state.pluginDirectory+'img/right-arrow.png">',
            prevArrow: '<img class="slick-arrow slick-prev" src="'+config.state.pluginDirectory+'img/left-arrow.png">',
            respondTo: 'min',
            responsive: [
            {
              breakpoint: 1440,
              settings: {
                slidesToShow: 4,
                slidesToScroll: 2
              }
            },
            {
              breakpoint: 740,
              settings: {
                slidesToShow: 3,
                slidesToScroll: 2
              }
            },
            {
              breakpoint: 425,
              settings: {
                slidesToShow: 2,
                slidesToScroll: 2
              }
            },
            {
              breakpoint: 375,
              settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 3000
              }
            }
            ]
          })
        })
      },
      
      error: function(xhr, err) {
        console.log(err)
      }
    })
  }

  /**
  ** dispatch events to personide connect
  **/

  Personide.dispatch = function(data) {

    var timestamp = new Date()
    timestamp = timestamp.toISOString()
    data = Object.assign(data, {eventTime: timestamp})

    if(data.entityType === 'user') {
      data = Object.assign(data, {entityId: ''})
    }

    var storage = JSON.parse(window.sessionStorage.getItem('PERSONIDE'))

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

      else if(data.event !== 'view') {
        console.log(storage.events)
        var sourceEvent = storage.events.find(function(item) {
          // @todo: check inside meta instead of just its existence
          return item.event === 'view' && item.meta !== undefined && item.targetEntityId == data.targetEntityId
        })
        if(sourceEvent !== undefined) {
          Object.assign(data, {
            meta: {
              category: config.state.currentPage,
              sourceElement: ''
            }
          })
        }
      }
    }

    // @todo: consider moving to ajax success
    storage.events.push(data)
    window.sessionStorage.setItem('PERSONIDE', JSON.stringify(storage))

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

        var history = window.localStorage.getItem('personide_history')

        if (history) {
          history = JSON.parse(history)
        } else {
          history = []
        }

        history.push(data)
        history = JSON.stringify(history)
        window.localStorage.setItem('personide_history', history)
      }
    })
  }

  /**
  ** Utility functions
  **/

  function getUrl(service_name) {
    var service = config.services[service_name]
    var url = 'http://' + service.host + ( (service.port)?':'+service.port:'' ) + '/' + service.endpoints.default
    return url
  }

  function getHeaders() {
    return {'Authorization': 'Bearer ' + config.accessKey}
  }

  function uuidv4() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
      var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
      return v.toString(16)
    })
  }

  function getCookie(name) {
    var result = document.cookie.split(';').filter(item => item.includes(' '+name+'='))
    if(result.length == 0)
      return null
    var pair = result.split('=')
    return {
      name: pair[0],
      value: pair[1]
    }
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


})( jQuery );