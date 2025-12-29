document.addEventListener('DOMContentLoaded', function() {

    // ======================
    // VARIABEL GLOBAL
    // ======================
    let currentOrder = [];
    const menuItems = document.querySelectorAll('.menu-item');
    const orderListDiv = document.getElementById('order-list');
    const totalPriceSpan = document.getElementById('total-price');
    const orderButton = document.getElementById('btn-order');
    const itemCountSpan = document.getElementById('item-count');
    const searchInput = document.getElementById('search-input');

    // Modal Bootstrap
    const confirmModal = new bootstrap.Modal(document.getElementById('confirmOrderModal'));
    const successModal = new bootstrap.Modal(document.getElementById('orderSuccessModal'));

    // Pembayaran
    const paymentMethodRadios = document.querySelectorAll('input[name="paymentMethod"]');
    const cashPaymentDiv = document.getElementById('cash-payment-details');
    const cashAmountInput = document.getElementById('cash-amount');
    const changeDisplayDiv = document.getElementById('change-display');
    const changeAmountSpan = document.getElementById('change-amount');
    const cashErrorDiv = document.getElementById('cash-error');
    const quickCashButtons = document.querySelectorAll('.quick-cash-btn');

    // ======================
    // UTILITAS
    // ======================
    function formatRupiah(angka) {
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function getCurrentTotal() {
        return currentOrder.reduce((sum, item) => sum + (item.price * item.qty), 0);
    }

    function getSelectedPaymentMethod() {
        return document.querySelector('input[name="paymentMethod"]:checked').value;
    }

    // ======================
    // KERANJANG
    // ======================
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
            orderButton.disabled = true; // Matikan tombol jika kosong
        } else {
            // Tombol jangan langsung di-enable, biarkan calculateChange yang memutuskan
            // berdasarkan kecukupan uang tunai nanti
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
                        <button class="btn btn-xs btn-link text-danger ms-1" onclick="removeItem('${item.id}')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                `;
                orderListDiv.appendChild(itemDiv);
            });
        }

        totalPriceSpan.innerText = `Rp ${formatRupiah(totalPrice)}`;
        itemCountSpan.innerText = `${totalItems} items`;
        calculateChange(); // Panggil validasi pembayaran setiap update keranjang
    }

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

    // ======================
    // PEMBAYARAN
    // ======================
    function calculateChange() {
        const total = getCurrentTotal();
        const method = getSelectedPaymentMethod();

        // JIKA KERANJANG KOSONG, TOMBOL HARUS MATI APAPUN METODENYA
        if (total === 0) {
            orderButton.disabled = true;
            cashErrorDiv.style.display = 'none';
            changeDisplayDiv.style.display = 'none';
            return;
        }

        if (method !== 'Tunai') {
            cashErrorDiv.style.display = 'none';
            changeDisplayDiv.style.display = 'none';
            orderButton.disabled = false; // QRIS tidak butuh cek uang tunai
            return;
        }

        const cashPaid = parseInt(cashAmountInput.value) || 0;

        if (cashPaid < total) {
            cashErrorDiv.style.display = 'block';
            changeDisplayDiv.style.display = 'none';
            orderButton.disabled = true; // Uang kurang, tombol mati
        } else {
            cashErrorDiv.style.display = 'none';
            changeDisplayDiv.style.display = 'block';
            changeAmountSpan.innerText = `Rp ${formatRupiah(cashPaid - total)}`;
            orderButton.disabled = false; // Uang cukup, tombol aktif
        }
    }

    // ======================
    // PROSES PESANAN
    // ======================
    orderButton.addEventListener('click', function() {
        const total = getCurrentTotal();
        const method = getSelectedPaymentMethod();

        document.getElementById('confirm-method').innerText = method;
        document.getElementById('confirm-total').innerText = `Rp ${formatRupiah(total)}`;

        if (method === 'Tunai') {
            const cash = parseInt(cashAmountInput.value) || 0;
            document.getElementById('confirm-cash-section').style.display = 'block';
            document.getElementById('confirm-cash-info').innerText =
                `Bayar: Rp ${formatRupiah(cash)} | Kembali: Rp ${formatRupiah(cash - total)}`;
        } else {
            document.getElementById('confirm-cash-section').style.display = 'none';
        }

        confirmModal.show();
    });

    // ======================
    // KIRIM KE SERVER & CETAK
    // ======================
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

                // 🔥 ARAHKAN KE NOTA
                setTimeout(() => {
                    window.open(
                        'nota.php?id=' + result.order_id,
                        '_blank'
                    );

                    // RESET KASIR TOTAL
                    currentOrder = [];
                    cashAmountInput.value = '';
                    changeDisplayDiv.style.display = 'none';
                    
                    // Update tampilan akan memicu orderButton.disabled = true di dalam updateOrderDisplay
                    updateOrderDisplay(); 
                    
                    successModal.hide();
                }, 500);

            } else {
                alert("Gagal menyimpan: " + result.message);
            }

        } catch (err) {
            console.error(err);
            alert("Koneksi server bermasalah.");
        }
    });

    // ======================
    // EVENT LAIN
    // ======================
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