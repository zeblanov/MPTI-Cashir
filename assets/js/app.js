document.addEventListener('DOMContentLoaded', function() {

    // VARIABEL GLOBAL
    let currentOrder = [];
    const menuItems = document.querySelectorAll('.menu-item');
    const orderListDiv = document.getElementById('order-list');
    const totalPriceSpan = document.getElementById('total-price');
    const orderButton = document.getElementById('btn-order');
    const itemCountSpan = document.getElementById('item-count');
    const searchInput = document.getElementById('search-input');

    // Instance Modal Bootstrap
    const confirmModal = new bootstrap.Modal(document.getElementById('confirmOrderModal'));
    const successModal = new bootstrap.Modal(document.getElementById('orderSuccessModal'));

    // Variabel Pembayaran
    const paymentMethodRadios = document.querySelectorAll('input[name="paymentMethod"]');
    const cashPaymentDiv = document.getElementById('cash-payment-details');
    const cashAmountInput = document.getElementById('cash-amount');
    const changeDisplayDiv = document.getElementById('change-display');
    const changeAmountSpan = document.getElementById('change-amount');
    const cashErrorDiv = document.getElementById('cash-error');
    const quickCashButtons = document.querySelectorAll('.quick-cash-btn');

    // UTILITY FUNCTIONS
    function formatRupiah(angka) {
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function getCurrentTotal() {
        return currentOrder.reduce((sum, item) => sum + (item.price * item.qty), 0);
    }

    function getSelectedPaymentMethod() {
        return document.querySelector('input[name="paymentMethod"]:checked').value;
    }

    // CORE LOGIC: KERANJANG
    function addItem(id, name, price) {
        const existingItem = currentOrder.find(item => String(item.id) === String(id));
        if (existingItem) {
            existingItem.qty += 1;
        } else {
            currentOrder.push({ id, name, price, qty: 1 });
        }
        updateOrderDisplay();
    }

    function updateOrderDisplay() {
        orderListDiv.innerHTML = '';
        let totalPrice = 0;
        let totalItems = 0;

        if (currentOrder.length === 0) {
            orderListDiv.innerHTML = '<p class="text-center text-muted mt-5">Silahkan Pilih Menu...</p>';
            orderButton.disabled = true;
        } else {
            orderButton.disabled = false;
            currentOrder.forEach(item => {
                const subtotal = item.price * item.qty;
                totalPrice += subtotal;
                totalItems += item.qty;

                const itemDiv = document.createElement('div');
                itemDiv.className = 'd-flex justify-content-between align-items-center border-bottom py-2';
                itemDiv.innerHTML = `
                    <div class="flex-grow-1">
                        <h6 class="mb-0 small fw-bold">${item.name}</h6>
                        <small class="text-muted">Rp ${formatRupiah(item.price)}</small>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <button class="btn btn-xs btn-outline-secondary" onclick="changeQty('${item.id}', -1)">-</button>
                        <span class="small mx-1">${item.qty}</span>
                        <button class="btn btn-xs btn-outline-secondary" onclick="changeQty('${item.id}', 1)">+</button>
                        <button class="btn btn-xs btn-link text-danger ms-1" onclick="removeItem('${item.id}')"><i class="bi bi-trash"></i></button>
                    </div>
                `;
                orderListDiv.appendChild(itemDiv);
            });
        }
        totalPriceSpan.innerText = `Rp ${formatRupiah(totalPrice)}`;
        itemCountSpan.innerText = `${totalItems} items`;
        calculateChange();
    }

    // Global Access untuk Button di dalam HTML String
    window.changeQty = (id, amt) => {
        const item = currentOrder.find(i => String(i.id) === String(id));
        if (item) {
            item.qty += amt;
            if (item.qty <= 0) removeItem(id);
            else updateOrderDisplay();
        }
    };

    window.removeItem = (id) => {
        currentOrder = currentOrder.filter(i => String(i.id) !== String(id));
        updateOrderDisplay();
    };

    // LOGIKA PEMBAYARAN
    function calculateChange() {
        const total = getCurrentTotal();
        const method = getSelectedPaymentMethod();
        
        if (method !== 'Tunai') {
            cashErrorDiv.style.display = 'none';
            changeDisplayDiv.style.display = 'none';
            orderButton.disabled = (total === 0);
            return;
        }

        const cashPaid = parseInt(cashAmountInput.value) || 0;
        if (total > 0 && cashPaid < total) {
            cashErrorDiv.style.display = 'block';
            changeDisplayDiv.style.display = 'none';
            orderButton.disabled = true;
        } else if (total > 0 && cashPaid >= total) {
            cashErrorDiv.style.display = 'none';
            changeDisplayDiv.style.display = 'block';
            changeAmountSpan.innerText = `Rp ${formatRupiah(cashPaid - total)}`;
            orderButton.disabled = false;
        }
    }

    // ALUR PROSES PESANAN (MENGGUNAKAN DUA MODAL)
    
    // Tahap 1: Klik Selesaikan (Buka Modal Konfirmasi)
    orderButton.addEventListener('click', function() {
        const total = getCurrentTotal();
        const method = getSelectedPaymentMethod();
        
        // Update UI Modal Konfirmasi
        document.getElementById('confirm-method').innerText = method;
        document.getElementById('confirm-total').innerText = `Rp ${formatRupiah(total)}`;
        
        if (method === 'Tunai') {
            const cash = parseInt(cashAmountInput.value) || 0;
            const change = cash - total;
            document.getElementById('confirm-cash-section').style.display = 'block';
            document.getElementById('confirm-cash-info').innerText = `Bayar: ${formatRupiah(cash)} / Kemb: ${formatRupiah(change)}`;
        } else {
            document.getElementById('confirm-cash-section').style.display = 'none';
        }

        confirmModal.show();
    });

    // Tahap 2: Klik Konfirmasi di Modal (Kirim ke Server)
    document.getElementById('confirm-final-submit').addEventListener('click', async function() {
        confirmModal.hide();
        
        const payload = {
            items: currentOrder,
            payment_method: getSelectedPaymentMethod(),
            total_price: getCurrentTotal(),
            cash_paid: parseInt(cashAmountInput.value) || getCurrentTotal()
        };

        try {
            const response = await fetch('proses_pesanan.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();

            if (result.success) {
                successModal.show();
            } else {
                alert("Gagal menyimpan: " + result.message);
            }
        } catch (e) {
            console.error(e);
            alert("Terjadi kesalahan koneksi server.");
        }
    });

    // EVENT LISTENERS LAINNYA
    menuItems.forEach(item => {
        item.addEventListener('click', () => {
            addItem(item.dataset.id, item.dataset.name, parseInt(item.dataset.price));
        });
    });

    paymentMethodRadios.forEach(r => {
        r.addEventListener('change', () => {
            cashPaymentDiv.style.display = (r.value === 'Tunai') ? 'block' : 'none';
            calculateChange();
        });
    });

    cashAmountInput.addEventListener('input', calculateChange);

    quickCashButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            cashAmountInput.value = btn.dataset.amount;
            calculateChange();
        });
    });

    searchInput.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.menu-card-item').forEach(card => {
            card.style.display = card.dataset.name.includes(q) ? 'block' : 'none';
        });
    });

});