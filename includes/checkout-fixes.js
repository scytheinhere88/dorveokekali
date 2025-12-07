/**
 * CHECKOUT PAGE FIXES
 * - Address autofill from saved addresses
 * - Shipping method auto-load
 * - Voucher functionality
 * - Form validation
 */

// ============================================
// ADDRESS AUTOFILL FIX
// ============================================

function initializeAddressAutofill() {
    const addressSelector = document.querySelector('select[name="saved_address_id"]');
    if (!addressSelector) return;

    addressSelector.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];

        if (!selectedOption || this.value === '') {
            // Clear fields if no address selected
            clearAddressFields();
            return;
        }

        // Get address data from option attributes
        const addressData = {
            recipient_name: selectedOption.dataset.recipientName || '',
            phone: selectedOption.dataset.phone || '',
            address: selectedOption.dataset.address || '',
            latitude: selectedOption.dataset.latitude || '',
            longitude: selectedOption.dataset.longitude || ''
        };

        // Fill in the form fields
        fillAddressFields(addressData);

        // Trigger shipping calculation
        console.log('Address selected, triggering shipping calculation...');
        setTimeout(() => {
            calculateShipping();
        }, 300);
    });

    // Auto-load shipping if default address is selected
    if (addressSelector.value) {
        const event = new Event('change');
        addressSelector.dispatchEvent(event);
    }
}

function fillAddressFields(data) {
    const fields = {
        'recipient_name': data.recipient_name,
        'phone': data.phone,
        'address': data.address,
        'latitude': data.latitude,
        'longitude': data.longitude
    };

    for (const [name, value] of Object.entries(fields)) {
        const field = document.querySelector(`[name="${name}"]`);
        if (field) {
            field.value = value || '';
            // Trigger change event for validation
            field.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    console.log('Address fields filled:', data);
}

function clearAddressFields() {
    const fieldNames = ['recipient_name', 'phone', 'address', 'latitude', 'longitude'];
    fieldNames.forEach(name => {
        const field = document.querySelector(`[name="${name}"]`);
        if (field) field.value = '';
    });
}

// ============================================
// SHIPPING METHOD AUTO-CALCULATION
// ============================================

let shippingCalculationTimeout = null;

async function calculateShipping() {
    // Clear previous timeout
    if (shippingCalculationTimeout) {
        clearTimeout(shippingCalculationTimeout);
    }

    // Get required fields
    const address = document.querySelector('[name="address"]');
    const latitude = document.querySelector('[name="latitude"]');
    const longitude = document.querySelector('[name="longitude"]');

    const shippingContainer = document.getElementById('shipping-methods-container');
    if (!shippingContainer) {
        console.error('Shipping container not found');
        return;
    }

    // Validate required fields
    if (!address || !address.value.trim()) {
        console.log('Address not filled yet');
        showShippingMessage('Please enter your address first');
        return;
    }

    if (!latitude || !longitude || !latitude.value || !longitude.value) {
        console.log('Location coordinates missing');
        showShippingMessage('Please select address to calculate shipping rates...');
        return;
    }

    // Show loading state
    showShippingLoading();

    // Delay to avoid too many requests
    shippingCalculationTimeout = setTimeout(async () => {
        try {
            console.log('Fetching shipping rates...');

            const formData = new FormData();
            formData.append('latitude', latitude.value);
            formData.append('longitude', longitude.value);
            formData.append('address', address.value);

            const response = await fetch('/api/shipping/calculate-rates.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('Shipping response:', data);

            if (data.success && data.rates && data.rates.length > 0) {
                displayShippingMethods(data.rates);
            } else {
                showShippingMessage(data.message || 'No shipping methods available for this location');
            }
        } catch (error) {
            console.error('Shipping calculation error:', error);
            showShippingMessage('Unable to load shipping methods. Please try again.');
        }
    }, 800); // 800ms delay to avoid rapid requests
}

function showShippingLoading() {
    const container = document.getElementById('shipping-methods-container');
    if (!container) return;

    container.innerHTML = `
        <div class="shipping-loading">
            <div class="shipping-loading-spinner"></div>
            <p class="shipping-loading-text">Calculating shipping rates...</p>
        </div>
    `;
}

function showShippingMessage(message) {
    const container = document.getElementById('shipping-methods-container');
    if (!container) return;

    container.innerHTML = `
        <div class="shipping-loading">
            <p class="shipping-loading-text">${message}</p>
        </div>
    `;
}

function displayShippingMethods(rates) {
    const container = document.getElementById('shipping-methods-container');
    if (!container) return;

    let html = '<div class="shipping-methods">';

    rates.forEach((rate, index) => {
        const isFirst = index === 0;
        html += `
            <label class="shipping-method-card ${isFirst ? 'selected' : ''}" data-rate-id="${rate.id || index}">
                <input type="radio" name="shipping_method" value="${rate.code || rate.courier}"
                       data-price="${rate.price || 0}"
                       ${isFirst ? 'checked' : ''}
                       style="display: none;">
                <div class="shipping-method-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                    </svg>
                </div>
                <div class="shipping-method-info">
                    <div class="shipping-method-name">${rate.name || rate.service || 'Standard Shipping'}</div>
                    <div class="shipping-method-eta">${rate.etd || rate.estimated_delivery || '2-3 days'}</div>
                </div>
                <div class="shipping-method-price">
                    ${rate.price == 0 ? 'FREE' : 'Rp ' + formatRupiah(rate.price || 0)}
                </div>
            </label>
        `;
    });

    html += '</div>';
    container.innerHTML = html;

    // Add click handlers
    const cards = container.querySelectorAll('.shipping-method-card');
    cards.forEach(card => {
        card.addEventListener('click', function() {
            // Remove selected from all
            cards.forEach(c => c.classList.remove('selected'));
            // Add selected to clicked
            this.classList.add('selected');
            // Check radio
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                // Update order summary
                updateOrderSummary();
            }
        });
    });

    // Update order summary with first shipping method
    updateOrderSummary();
}

function formatRupiah(amount) {
    return new Intl.NumberFormat('id-ID').format(amount);
}

// ============================================
// ORDER SUMMARY UPDATE
// ============================================

function updateOrderSummary() {
    const selectedShipping = document.querySelector('input[name="shipping_method"]:checked');
    if (!selectedShipping) return;

    const shippingPrice = parseFloat(selectedShipping.dataset.price || 0);

    // Update shipping cost display
    const shippingCostEl = document.getElementById('shipping-cost');
    if (shippingCostEl) {
        shippingCostEl.textContent = shippingPrice == 0 ? 'FREE' : 'Rp ' + formatRupiah(shippingPrice);
    }

    // Calculate total
    const subtotalEl = document.getElementById('subtotal-amount');
    const discountEl = document.getElementById('discount-amount');
    const totalEl = document.getElementById('total-amount');

    if (subtotalEl && totalEl) {
        const subtotal = parseFloat(subtotalEl.dataset.value || 0);
        const discount = parseFloat(discountEl?.dataset.value || 0);
        const total = subtotal - discount + shippingPrice;

        totalEl.textContent = 'Rp ' + formatRupiah(total);
        totalEl.dataset.value = total;
    }
}

// ============================================
// VOUCHER FUNCTIONALITY
// ============================================

async function applyVoucher() {
    const voucherInput = document.getElementById('voucher-code');
    if (!voucherInput) return;

    const code = voucherInput.value.trim().toUpperCase();
    if (!code) {
        alert('Please enter a voucher code');
        return;
    }

    // Show loading
    const applyBtn = document.querySelector('.btn-apply-voucher');
    const originalText = applyBtn ? applyBtn.textContent : '';
    if (applyBtn) applyBtn.textContent = 'Checking...';

    try {
        const response = await fetch('/api/vouchers/validate.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `code=${encodeURIComponent(code)}`
        });

        const data = await response.json();

        if (data.success && data.voucher) {
            // Show applied voucher
            showAppliedVoucher(data.voucher);
            voucherInput.value = '';
            updateOrderSummary();
        } else {
            alert(data.message || 'Invalid voucher code');
        }
    } catch (error) {
        console.error('Voucher validation error:', error);
        alert('Unable to apply voucher. Please try again.');
    } finally {
        if (applyBtn) applyBtn.textContent = originalText;
    }
}

function showAppliedVoucher(voucher) {
    const container = document.getElementById('applied-voucher-container');
    if (!container) return;

    const discountAmount = voucher.discount_amount || 0;
    const discountPercent = voucher.discount_percent || 0;

    let discountText = '';
    if (discountPercent > 0) {
        discountText = `-${discountPercent}%`;
    } else if (discountAmount > 0) {
        discountText = `-Rp ${formatRupiah(discountAmount)}`;
    }

    container.innerHTML = `
        <div class="applied-voucher">
            <div>
                <div class="applied-voucher-code">${voucher.code}</div>
                <div class="applied-voucher-discount">${discountText}</div>
            </div>
            <span class="btn-remove-voucher" onclick="removeVoucher()" title="Remove voucher">×</span>
        </div>
    `;

    // Update hidden input
    const hiddenInput = document.getElementById('voucher-code-hidden');
    if (hiddenInput) {
        hiddenInput.value = voucher.code;
    }

    // Update discount in summary
    const discountEl = document.getElementById('discount-amount');
    if (discountEl) {
        const calculatedDiscount = discountPercent > 0
            ? (parseFloat(document.getElementById('subtotal-amount')?.dataset.value || 0) * discountPercent / 100)
            : discountAmount;

        discountEl.textContent = '-Rp ' + formatRupiah(calculatedDiscount);
        discountEl.dataset.value = calculatedDiscount;
    }
}

function removeVoucher() {
    const container = document.getElementById('applied-voucher-container');
    if (container) container.innerHTML = '';

    const hiddenInput = document.getElementById('voucher-code-hidden');
    if (hiddenInput) hiddenInput.value = '';

    // Reset discount
    const discountEl = document.getElementById('discount-amount');
    if (discountEl) {
        discountEl.textContent = 'Rp 0';
        discountEl.dataset.value = '0';
    }

    updateOrderSummary();
}

// ============================================
// FORM VALIDATION
// ============================================

function validateCheckoutForm() {
    const form = document.getElementById('checkout-form');
    if (!form) return false;

    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    let firstInvalidField = null;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('error');
            if (!firstInvalidField) {
                firstInvalidField = field;
            }
        } else {
            field.classList.remove('error');
        }
    });

    // Check if shipping method is selected
    const shippingMethod = document.querySelector('input[name="shipping_method"]:checked');
    if (!shippingMethod) {
        alert('Please select a shipping method');
        isValid = false;
    }

    if (!isValid && firstInvalidField) {
        firstInvalidField.focus();
        firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    return isValid;
}

// Add error styles
const style = document.createElement('style');
style.textContent = `
    .form-group input.error,
    .form-group select.error,
    .form-group textarea.error {
        border-color: #EF4444 !important;
        background: #FEE2E2 !important;
    }
`;
document.head.appendChild(style);

// ============================================
// INITIALIZATION
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing checkout fixes...');

    // Initialize address autofill
    initializeAddressAutofill();

    // Listen for manual address changes
    const addressField = document.querySelector('[name="address"]');
    if (addressField) {
        addressField.addEventListener('blur', function() {
            const lat = document.querySelector('[name="latitude"]');
            const lng = document.querySelector('[name="longitude"]');
            if (lat && lng && lat.value && lng.value) {
                calculateShipping();
            }
        });
    }

    // Voucher apply button
    const applyVoucherBtn = document.querySelector('.btn-apply-voucher');
    if (applyVoucherBtn) {
        applyVoucherBtn.addEventListener('click', applyVoucher);
    }

    // Voucher input enter key
    const voucherInput = document.getElementById('voucher-code');
    if (voucherInput) {
        voucherInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyVoucher();
            }
        });
    }

    // Checkout form validation
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            if (!validateCheckoutForm()) {
                e.preventDefault();
                return false;
            }
        });
    }

    console.log('Checkout fixes initialized!');
});

// Make functions globally accessible
window.calculateShipping = calculateShipping;
window.applyVoucher = applyVoucher;
window.removeVoucher = removeVoucher;
window.updateOrderSummary = updateOrderSummary;
