// Classic Motorcycle Parts PH - Custom JavaScript

// Import Bootstrap
const bootstrap = window.bootstrap

document.addEventListener("DOMContentLoaded", () => {
  // Initialize tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl))

  // Initialize popovers
  var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
  var popoverList = popoverTriggerList.map((popoverTriggerEl) => new bootstrap.Popover(popoverTriggerEl))

  // Smooth scrolling for anchor links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault()
      const target = document.querySelector(this.getAttribute("href"))
      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        })
      }
    })
  })

  // Auto-hide alerts after 5 seconds
  setTimeout(() => {
    const alerts = document.querySelectorAll(".alert:not(.alert-permanent)")
    alerts.forEach((alert) => {
      const bsAlert = new bootstrap.Alert(alert)
      bsAlert.close()
    })
  }, 5000)

  // Form validation enhancement
  const forms = document.querySelectorAll(".needs-validation")
  forms.forEach((form) => {
    form.addEventListener("submit", (event) => {
      if (!form.checkValidity()) {
        event.preventDefault()
        event.stopPropagation()
      }
      form.classList.add("was-validated")
    })
  })

  // Search functionality
  const searchForm = document.getElementById("searchForm")
  if (searchForm) {
    searchForm.addEventListener("submit", function (e) {
      const searchInput = this.querySelector('input[name="search"]')
      if (searchInput.value.trim() === "") {
        e.preventDefault()
        searchInput.focus()
      }
    })
  }

  // Quantity controls in cart
  setupQuantityControls()

  // Image lazy loading
  setupLazyLoading()

  // Back to top button
  setupBackToTop()
})

// Quantity controls for cart and product pages
function setupQuantityControls() {
  document.querySelectorAll(".quantity-controls").forEach((controls) => {
    const minusBtn = controls.querySelector(".quantity-minus")
    const plusBtn = controls.querySelector(".quantity-plus")
    const input = controls.querySelector(".quantity-input")

    if (minusBtn && plusBtn && input) {
      minusBtn.addEventListener("click", () => {
        const value = Number.parseInt(input.value)
        if (value > 1) {
          input.value = value - 1
          updateCartQuantity(input)
        }
      })

      plusBtn.addEventListener("click", () => {
        const value = Number.parseInt(input.value)
        const max = Number.parseInt(input.getAttribute("max")) || 999
        if (value < max) {
          input.value = value + 1
          updateCartQuantity(input)
        }
      })

      input.addEventListener("change", function () {
        updateCartQuantity(this)
      })
    }
  })
}

// Update cart quantity via AJAX
function updateCartQuantity(input) {
  const productId = input.getAttribute("data-product-id")
  const quantity = Number.parseInt(input.value)

  if (!productId) return

  fetch("api/cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `action=update&product_id=${productId}&quantity=${quantity}&csrf_token=${window.csrfToken}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Update cart total
        updateCartTotal(data.cart_total)
        // Update cart count in navbar
        updateCartCount(data.cart_count)
        // Show success message
        showNotification("Cart updated successfully", "success")
      } else {
        showNotification(data.message || "Error updating cart", "error")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showNotification("Error updating cart", "error")
    })
}

// Add to cart functionality
function addToCart(productId, quantity = 1) {
  const formData = new FormData()
  formData.append("action", "add")
  formData.append("product_id", productId)
  formData.append("quantity", quantity)
  formData.append("csrf_token", window.csrfToken)

  fetch("api/cart.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        updateCartCount(data.cart_count)
        showNotification("Product added to cart successfully!", "success")
      } else {
        showNotification(data.message || "Error adding product to cart", "error")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showNotification("Error adding product to cart", "error")
    })
}

// Remove from cart
function removeFromCart(productId) {
  if (!confirm("Are you sure you want to remove this item from your cart?")) {
    return
  }

  fetch("api/cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `action=remove&product_id=${productId}&csrf_token=${window.csrfToken}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Remove the cart item from DOM
        const cartItem = document.querySelector(`[data-product-id="${productId}"]`).closest(".cart-item")
        if (cartItem) {
          cartItem.remove()
        }

        updateCartTotal(data.cart_total)
        updateCartCount(data.cart_count)
        showNotification("Item removed from cart", "success")

        // If cart is empty, show empty cart message
        if (data.cart_count === 0) {
          location.reload()
        }
      } else {
        showNotification(data.message || "Error removing item", "error")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showNotification("Error removing item", "error")
    })
}

// Update cart total display
function updateCartTotal(total) {
  const totalElements = document.querySelectorAll(".cart-total")
  totalElements.forEach((element) => {
    element.textContent = "₱" + Number.parseFloat(total).toLocaleString("en-PH", { minimumFractionDigits: 2 })
  })
}

// Update cart count in navbar
function updateCartCount(count) {
  const cartBadges = document.querySelectorAll(".navbar .badge, .cart-count")
  cartBadges.forEach((badge) => {
    badge.textContent = count
    badge.style.display = count > 0 ? "inline" : "none"
  })
}

// Show notification
function showNotification(message, type = "info") {
  const alertClass =
    type === "success"
      ? "alert-success"
      : type === "error"
        ? "alert-danger"
        : type === "warning"
          ? "alert-warning"
          : "alert-info"

  const alertDiv = document.createElement("div")
  alertDiv.className = `alert ${alertClass} alert-dismissible fade show position-fixed`
  alertDiv.style.cssText = "top: 100px; right: 20px; z-index: 9999; min-width: 300px;"
  alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `

  document.body.appendChild(alertDiv)

  // Auto remove after 5 seconds
  setTimeout(() => {
    if (alertDiv.parentNode) {
      alertDiv.remove()
    }
  }, 5000)
}

// Image lazy loading
function setupLazyLoading() {
  const images = document.querySelectorAll("img[data-src]")

  if ("IntersectionObserver" in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const img = entry.target
          img.src = img.dataset.src
          img.classList.remove("lazy")
          imageObserver.unobserve(img)
        }
      })
    })

    images.forEach((img) => imageObserver.observe(img))
  } else {
    // Fallback for older browsers
    images.forEach((img) => {
      img.src = img.dataset.src
    })
  }
}

// Back to top button
function setupBackToTop() {
  const backToTopBtn = document.createElement("button")
  backToTopBtn.innerHTML = '<i class="fas fa-chevron-up"></i>'
  backToTopBtn.className = "btn btn-warning position-fixed"
  backToTopBtn.style.cssText =
    "bottom: 20px; right: 20px; z-index: 999; border-radius: 50%; width: 50px; height: 50px; display: none;"
  backToTopBtn.setAttribute("aria-label", "Back to top")

  document.body.appendChild(backToTopBtn)

  // Show/hide button based on scroll position
  window.addEventListener("scroll", () => {
    if (window.pageYOffset > 300) {
      backToTopBtn.style.display = "block"
    } else {
      backToTopBtn.style.display = "none"
    }
  })

  // Scroll to top when clicked
  backToTopBtn.addEventListener("click", () => {
    window.scrollTo({
      top: 0,
      behavior: "smooth",
    })
  })
}

// Price formatting
function formatPrice(price) {
  return "₱" + Number.parseFloat(price).toLocaleString("en-PH", { minimumFractionDigits: 2 })
}

// Form submission with loading state
function submitFormWithLoading(form, submitBtn) {
  const originalText = submitBtn.innerHTML
  submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...'
  submitBtn.disabled = true

  // Reset button after 10 seconds (fallback)
  setTimeout(() => {
    submitBtn.innerHTML = originalText
    submitBtn.disabled = false
  }, 10000)
}

// Search suggestions (if implemented)
function setupSearchSuggestions() {
  const searchInput = document.querySelector('input[name="search"]')
  if (!searchInput) return

  let searchTimeout

  searchInput.addEventListener("input", function () {
    clearTimeout(searchTimeout)
    const query = this.value.trim()

    if (query.length < 2) {
      hideSuggestions()
      return
    }

    searchTimeout = setTimeout(() => {
      fetchSearchSuggestions(query)
    }, 300)
  })
}

function fetchSearchSuggestions(query) {
  fetch(`api/search-suggestions.php?q=${encodeURIComponent(query)}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.suggestions.length > 0) {
        showSuggestions(data.suggestions)
      } else {
        hideSuggestions()
      }
    })
    .catch((error) => {
      console.error("Error fetching suggestions:", error)
      hideSuggestions()
    })
}

function showSuggestions(suggestions) {
  // Implementation for showing search suggestions dropdown
  // This would create a dropdown below the search input
}

function hideSuggestions() {
  // Implementation for hiding search suggestions
}

// Product image gallery (if implemented)
function setupProductGallery() {
  const thumbnails = document.querySelectorAll(".product-thumbnail")
  const mainImage = document.querySelector(".product-main-image")

  if (!thumbnails.length || !mainImage) return

  thumbnails.forEach((thumb) => {
    thumb.addEventListener("click", function () {
      const newSrc = this.getAttribute("data-image")
      if (newSrc) {
        mainImage.src = newSrc

        // Update active thumbnail
        thumbnails.forEach((t) => t.classList.remove("active"))
        this.classList.add("active")
      }
    })
  })
}

// Initialize product gallery if on product page
if (window.location.pathname.includes("product.php")) {
  setupProductGallery()
}

// Export functions for global use
window.addToCart = addToCart
window.removeFromCart = removeFromCart
window.showNotification = showNotification
window.formatPrice = formatPrice
window.submitFormWithLoading = submitFormWithLoading
