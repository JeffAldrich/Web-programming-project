/* =========================================================
   STORE CATEGORY FILTER
========================================================= */

const categoryButtons = document.querySelectorAll('.store-categories button')

const products = document.querySelectorAll('.store-product')

categoryButtons.forEach(button => {
  button.addEventListener('click', function () {
    const selectedCategory = this.dataset.category

    categoryButtons.forEach(btn => {
      btn.classList.remove('active')
    })

    this.classList.add('active')

    products.forEach(product => {
      const productCategory = product.dataset.category

      if (selectedCategory === 'all' || productCategory === selectedCategory) {
        product.style.display = 'block'
      } else {
        product.style.display = 'none'
      }
    })
  })
})

/* =========================================================
   ACCOUNT DROPDOWN
========================================================= */

document.addEventListener('DOMContentLoaded', function () {
  const accountButton = document.querySelector('.account-button')

  const accountMenu = document.querySelector('.account-menu')

  const accountDropdown = document.querySelector('.account-dropdown')

  if (!accountButton || !accountMenu) {
    return
  }

  accountButton.addEventListener('click', function (event) {
    event.preventDefault()
    event.stopPropagation()

    accountMenu.classList.toggle('active')
  })

  if (accountDropdown) {
    accountDropdown.addEventListener('click', function (event) {
      event.stopPropagation()
    })
  }

  document.addEventListener('click', function (event) {
    if (!accountMenu.contains(event.target)) {
      accountMenu.classList.remove('active')
    }
  })
})

/* =========================================================
   PRODUCT SIZE SELECTION
   (backup handler — product.php has its own)
========================================================= */

const sizeButtons = document.querySelectorAll('.size-options button')

sizeButtons.forEach(button => {
  button.addEventListener('click', function () {
    sizeButtons.forEach(btn => {
      btn.classList.remove('selected')
    })

    this.classList.add('selected')
  })
})

/* =========================================================
   PRODUCT COLOR SELECTION
   (backup handler — product.php has its own)
========================================================= */

const colorButtons = document.querySelectorAll('.color-options button')

colorButtons.forEach(button => {
  button.addEventListener('click', function () {
    colorButtons.forEach(btn => {
      btn.classList.remove('selected')
    })

    this.classList.add('selected')
  })
})

/* =========================================================
   PRODUCT SELECTION STYLE
========================================================= */

const selectionStyle = document.createElement('style')

selectionStyle.textContent = `

.size-options button.selected {
    background-color: black;
    color: white;
    border-color: black;
}

.color-options button.selected {
    outline: 3px solid black;
    outline-offset: 3px;
}

`

document.head.appendChild(selectionStyle)

/* =========================================================
   PASSWORD TOGGLE
========================================================= */

function togglePassword (inputId, button) {
  const input = document.getElementById(inputId)

  if (!input) {
    return
  }

  if (input.type === 'password') {
    input.type = 'text'

    button.textContent = '👁'
  } else {
    input.type = 'password'

    button.textContent = '👁'
  }
}

/* =========================================================
   LOGIN REQUIRED POPUP (close/cancel/esc)
========================================================= */

document.addEventListener('DOMContentLoaded', function () {
  const popup = document.getElementById('login-required-popup')

  const closeButton = document.querySelector('.login-popup-close')

  const cancelButton = document.querySelector('.login-popup-cancel')

  function closePopup () {
    if (popup) {
      popup.classList.remove('active')
      popup.classList.remove('show')
    }
  }

  if (closeButton) {
    closeButton.addEventListener('click', closePopup)
  }

  if (cancelButton) {
    cancelButton.addEventListener('click', closePopup)
  }

  if (popup) {
    popup.addEventListener('click', function (event) {
      if (event.target === popup) {
        closePopup()
      }
    })
  }

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      closePopup()
    }
  })
})

/* =========================================================
   TOAST NOTIFICATION
========================================================= */

function showToast (message, isSuccess) {
  var old = document.getElementById('jac-toast')
  if (old) old.remove()

  var toast = document.createElement('div')
  toast.id = 'jac-toast'
  toast.textContent = (isSuccess ? '✔ ' : '⚠ ') + message

  toast.style.position = 'fixed'
  toast.style.bottom = '25px'
  toast.style.right = '25px'
  toast.style.padding = '15px 25px'
  toast.style.background = isSuccess ? 'black' : '#8a2222'
  toast.style.color = 'white'
  toast.style.fontSize = '13px'
  toast.style.letterSpacing = '1px'
  toast.style.fontFamily = 'inherit'
  toast.style.boxShadow = '0 5px 15px rgba(0,0,0,0.3)'
  toast.style.zIndex = '9999'
  toast.style.opacity = '0'
  toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease'
  toast.style.transform = 'translateY(10px)'

  document.body.appendChild(toast)

  requestAnimationFrame(function () {
    toast.style.opacity = '1'
    toast.style.transform = 'translateY(0)'
  })

  setTimeout(function () {
    toast.style.opacity = '0'
    toast.style.transform = 'translateY(10px)'
    setTimeout(function () {
      toast.remove()
    }, 300)
  }, 2500)
}

/* =========================================================
   AJAX INTERCEPTOR — add-to-cart forms
   (catches form submits meant for add_to_cart.php)
========================================================= */

document.addEventListener('submit', function (e) {
  var form = e.target

  if (!form.action || form.action.indexOf('add_to_cart.php') === -1) {
    return
  }

  e.preventDefault()

  var button = form.querySelector("button[type='submit']")
  var originalText = button ? button.textContent : ''
  if (button) {
    button.disabled = true
    button.textContent = 'ADDING...'
  }

  var data = new FormData(form)
  data.append('ajax', '1')

  fetch(form.action, {
    method: 'POST',
    body: data
  })
    .then(function (response) {
      return response.json()
    })
    .then(function (result) {
      if (result.redirect) {
        window.location.href = result.redirect
        return
      }

      showToast(result.message, result.success)

      if (result.success && typeof result.count !== 'undefined') {
        var badges = document.querySelectorAll(
          '.cart-number, .notification-number'
        )
        badges.forEach(function (badge) {
          badge.textContent = result.count
        })
      }

      if (button) {
        button.disabled = false
        button.textContent = originalText
      }
    })
    .catch(function () {
      showToast('Something went wrong. Please try again.', false)

      if (button) {
        button.disabled = false
        button.textContent = originalText
      }
    })
})

/* =========================================================
   ACCOUNT BUTTON BADGE (database-driven)
   red total on the circle; opens = badge hides,
   counts appear inside the dropdown
========================================================= */

document.addEventListener('DOMContentLoaded', function () {

  var accountButton = document.querySelector('.account-button')

  var accountMenu = document.querySelector('.account-menu')

  var accountDropdown = document.querySelector('.account-dropdown')

  if (!accountButton || !accountMenu || !accountDropdown) {
    return
  }

  var counts = { orders: 0, history: 0, cart: 0, wishlist: 0, total: 0 }

  function getLink (keyword) {
    var links = accountDropdown.querySelectorAll('a')

    for (var i = 0; i < links.length; i++) {
      var href = decodeURIComponent(links[i].getAttribute('href') || '')

      if (href.indexOf(keyword) !== -1) {
        return links[i]
      }
    }

    return null
  }

  /* Insert a MY HISTORY link into the dropdown if the page
     doesn't have one (covers index, store, legal, etc.) */
  function ensureHistoryLink () {
    var existing = getLink('view=history')

    if (existing) {
      return existing
    }

    var ordersLink = getLink('my order')

    if (!ordersLink) {
      return null
    }

    var historyLink = document.createElement('a')

    historyLink.href = '/JAC/user%20details/my%20order.php?view=history'

    historyLink.textContent = 'MY HISTORY'

    ordersLink.insertAdjacentElement('afterend', historyLink)

    return historyLink
  }

  function addCountToLink (link, count, className) {
    if (!link || count <= 0) {
      return
    }

    var existing = link.querySelector('.' + className)

    if (!existing) {
      existing = document.createElement('span')

      existing.className = className

      link.appendChild(existing)
    }

    existing.textContent = count
  }

  function removeAllCounts () {
    var spans = accountDropdown.querySelectorAll(
      '.cart-number, .orders-number, .history-number, .wishlist-number'
    )

    spans.forEach(function (s) {
      s.remove()
    })
  }

  function renderMenuCounts () {
    removeAllCounts()

    addCountToLink(getLink('my cart'), counts.cart, 'cart-number')

    addCountToLink(getLink('my order'), counts.orders, 'orders-number')

    addCountToLink(ensureHistoryLink(), counts.history, 'history-number')

    addCountToLink(getLink('wishlist'), counts.wishlist, 'wishlist-number')
  }

  function showCircleBadge () {
    removeAllCounts()

    if (counts.total <= 0) {
      return
    }

    var badge = accountButton.querySelector('.notification-number')

    if (!badge) {
      badge = document.createElement('span')

      badge.className = 'notification-number'

      accountButton.appendChild(badge)
    }

    badge.textContent = counts.total
  }

  function hideCircleBadge () {
    var badge = accountButton.querySelector('.notification-number')

    if (badge) {
      badge.remove()
    }
  }

  fetch('/JAC/php/get_counts.php')
    .then(function (r) {
      return r.json()
    })
    .then(function (data) {
      if (!data.logged_in) {
        return
      }

      counts = data

      showCircleBadge()
    })
    .catch(function () {})

  var observer = new MutationObserver(function () {
    if (accountMenu.classList.contains('active')) {
      hideCircleBadge()
      renderMenuCounts()
    } else {
      showCircleBadge()
    }
  })

  observer.observe(accountMenu, {
    attributes: true,
    attributeFilter: ['class']
  })
})

/* =========================================================
   LOGGED-OUT DROPDOWN — LOG OUT becomes LOG IN + REGISTER
========================================================= */

document.addEventListener('DOMContentLoaded', function () {

  fetch('/JAC/php/get_counts.php')
    .then(function (r) {
      return r.json()
    })
    .then(function (data) {
      if (data.logged_in) {
        return
      }

      var logoutLink = document.querySelector('.account-dropdown .logout')

      if (!logoutLink) {
        return
      }

      /* Turn LOG OUT into LOG IN */
      logoutLink.textContent = 'LOG IN'
      logoutLink.href = '/JAC/login.php'
      logoutLink.classList.remove('logout')

      /* Add REGISTER right below it */
      var registerLink = document.createElement('a')
      registerLink.href = '/JAC/register.php'
      registerLink.textContent = 'REGISTER'
      logoutLink.insertAdjacentElement('afterend', registerLink)
    })
    .catch(function () {})
})

/* =========================================================
   NEWSLETTER SUBSCRIBE (AJAX + toast)
========================================================= */

document.addEventListener('DOMContentLoaded', function () {

  var form = document.getElementById('newsletter-form')

  if (!form) {
    return
  }

  form.addEventListener('submit', function (e) {

    e.preventDefault()

    var input = form.querySelector('input[type="email"]')

    var button = form.querySelector('button')

    var email = input ? input.value.trim() : ''

    if (!email) {
      showToast('Please enter your e-mail.', false)
      return
    }

    button.disabled = true

    var data = new FormData()
    data.append('email', email)

    fetch('/JAC/php/subscribe.php', {
      method: 'POST',
      body: data
    })
      .then(function (r) {
        return r.json()
      })
      .then(function (result) {
        showToast(result.message, result.success)

        if (result.success) {
          input.value = ''
        }
      })
      .catch(function () {
        showToast('Something went wrong. Please try again.', false)
      })
      .finally(function () {
        button.disabled = false
      })
  })
})