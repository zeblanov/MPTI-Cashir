document.addEventListener('DOMContentLoaded', function() {

    // VARIABEL GLOBAL
    let currentOrder = [];
    const menuItems = document.querySelectorAll('.menu-item');
    const orderListDiv = document.getElementById('order-list');
    const totalPriceSpan = document.getElementById('total-price');
    const orderButton = document.getElementById('btn-order');
    const itemCountSpan = document.getElementById('item-count');
    const searchInput = document.getElementById('search-input');

    // Variabel untuk Modal Notifikasi
    const successModalEl = document.getElementById('orderSuccessModal');
    // Cek keberadaan elemen sebelum membuat instance Modal
    const successModal = successModalEl ? new bootstrap.Modal(successModalEl) : null;

    // Variabel untuk Pembayaran
    const paymentMethodRadios = document.querySelectorAll('input[name="paymentMethod"]');
    const cashPaymentDiv = document.getElementById('cash-payment-details');
    const cashAmountInput = document.getElementById('cash-amount');
    const changeDisplayDiv = document.getElementById('change-display');
    const changeAmountSpan = document.getElementById('change-amount');
    const cashErrorDiv = document.getElementById('cash-error');
    const quickCashButtons = document.querySelectorAll('.quick-cash-btn'); // Sudah ada

    // FUNGSI UTAMA

    /** Mengubah angka menjadi format Rupiah. */
    function formatRupiah(angka) {
        if (angka == null) return '0';
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    /** Helper: Mendapatkan total harga pesanan saat ini (sebagai angka) */
    function getCurrentTotal() {
        return currentOrder.reduce((sum, item) => sum + (item.price * item.qty), 0);
    }

    /** Helper: Mendapatkan metode pembayaran yang dipilih */
    function getSelectedPaymentMethod() {
        const selectedRadio = document.querySelector('input[name="paymentMethod"]:checked');
        return selectedRadio ? selectedRadio.value : null;
    }
    
    /** Menambahkan item ke pesanan atau memperbarui jumlahnya. */
    function addItem(id, name, price) {
        // ID harus diubah ke string karena data-attribute dari HTML adalah string
        const existingItem = currentOrder.find(item => String(item.id) === String(id)); 
        if (existingItem) {
            existingItem.qty += 1;
        } else {
            currentOrder.push({ id, name, price, qty: 1 });
        }
        updateOrderDisplay();
    }

    /** Memperbarui tampilan daftar pesanan, total, dan memicu kalkulasi kembalian. */
    function updateOrderDisplay() {
        orderListDiv.innerHTML = '';
        let totalPrice = 0;
        let totalItems = 0;

        if (currentOrder.length === 0) {
            orderListDiv.innerHTML = '<p class="text-center text-muted mt-4">Silahkan Pilih Menu...</p>';
            orderButton.disabled = true;
        } else {
            orderButton.disabled = false;
            currentOrder.forEach(item => {
                const subtotal = item.price * item.qty;
                totalPrice += subtotal;
                totalItems += item.qty;

                const itemDiv = document.createElement('div');
                // *** PERBAIKAN: Struktur HTML yang sesuai dengan Bootstrap ***
                itemDiv.className = 'd-flex justify-content-between align-items-center border-bottom py-2';
                itemDiv.innerHTML = `
                    <div class="flex-grow-1">
                        <h6 class="mb-0">${item.name}</h6>
                        <small class="text-muted">Rp ${formatRupiah(item.price)}</small>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <button class="btn btn-sm btn-outline-secondary btn-qty-change" data-id="${item.id}" data-amount="-1">
                            <i class="bi bi-dash-lg"></i>
                        </button>
                        <span class="fw-bold mx-1">${item.qty}</span>
                        <button class="btn btn-sm btn-outline-secondary btn-qty-change" data-id="${item.id}" data-amount="1">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-remove-item ms-2" data-id="${item.id}">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                `;
                orderListDiv.appendChild(itemDiv);
            });
        }
        totalPriceSpan.innerText = `Rp ${formatRupiah(totalPrice)}`;
        itemCountSpan.innerText = `${totalItems} items`;

        // Panggil kalkulasi kembalian jika metode tunai aktif
        if (getSelectedPaymentMethod() === 'Tunai') {
            calculateChange();
        } else {
            // Sembunyikan error dan kembalian jika bukan tunai
            cashErrorDiv.style.display = 'none';
            changeDisplayDiv.style.display = 'none';
        }
    }

    /** Mengubah jumlah (quantity) dari sebuah item. */
    function changeQuantity(itemId, amount) {
        const item = currentOrder.find(i => String(i.id) === String(itemId));
        if (item) {
            item.qty += amount;
            if (item.qty <= 0) {
                removeItem(itemId);
            } else {
                updateOrderDisplay();
            }
        }
    }

    /** Menghapus sebuah item dari daftar pesanan. */
    function removeItem(itemId) {
        currentOrder = currentOrder.filter(i => String(i.id) !== String(itemId));
        updateOrderDisplay();
    }

    /** Menghitung dan menampilkan kembalian */
    function calculateChange() {
        const total = getCurrentTotal();
        const cashPaid = parseInt(cashAmountInput.value) || 0;

        cashErrorDiv.style.display = 'none';
        changeDisplayDiv.style.display = 'none';

        if (total <= 0) {
            cashAmountInput.value = '';
            return;
        }

        if (cashPaid < total) {
            cashErrorDiv.style.display = 'block';
            orderButton.disabled = true;
        } else {
            const change = cashPaid - total;
            changeAmountSpan.innerText = `Rp ${formatRupiah(change)}`;
            changeDisplayDiv.style.display = 'block';
            orderButton.disabled = false;
        }
    }
    
    /** Mengirim data pesanan ke backend termasuk info pembayaran. */
    async function processOrder() {
        if (currentOrder.length === 0) {
            alert('Keranjang masih kosong!');
            return;
        }

        const paymentMethod = getSelectedPaymentMethod();
        const total = getCurrentTotal();
        let cashPaid = 0;
        let change = 0;

        // Validasi khusus untuk pembayaran Tunai
        if (paymentMethod === 'Tunai') {
            cashPaid = parseInt(cashAmountInput.value) || 0;
            if (cashPaid < total) {
                alert('Jumlah uang tunai yang dimasukkan kurang!');
                cashAmountInput.focus();
                return;
            }
            change = cashPaid - total;
        } else if (paymentMethod === 'QRIS') {
            cashPaid = total;
            change = 0;
        } else {
             alert('Silakan pilih metode pembayaran.');
             return;
        }

        // Siapkan data lengkap untuk dikirim ke backend
        const orderPayload = {
            items: currentOrder,
            payment_method: paymentMethod,
            total_price: total,
            cash_paid: cashPaid,
            change_amount: change,
            // Tambahkan Order ID yang unik (jika Anda memiliki logic di PHP untuk ini, hapus ini)
            // order_id: `ORD-${Date.now()}` 
        };

        // Kirim data ke backend
        try {
            const response = await fetch('proses_pesanan.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(orderPayload)
            });
            const result = await response.json();

            if (result.success) {
                // Tampilkan Modal sukses
                if(successModal) {
                    successModal.show();
                } else {
                    alert('Pesanan Berhasil!');
                }
                
                // Reset UI
                currentOrder = [];
                updateOrderDisplay();
                cashAmountInput.value = '';
                changeDisplayDiv.style.display = 'none';
                cashErrorDiv.style.display = 'none';
                document.getElementById('pay-cash').checked = true;
                cashPaymentDiv.style.display = 'block';

            } else {
                alert('Gagal memproses pesanan: ' + (result.message || 'Error tidak diketahui'));
            }
        } catch (error) {
            console.error('Error saat proses order:', error);
            alert('Terjadi kesalahan koneksi saat memproses pesanan.');
        }
    }

    // EVENT LISTENERS

    // 1. Event klik untuk setiap item menu
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const price = parseInt(this.dataset.price);
            addItem(id, name, price);
        });
    });

    // 2. Event klik untuk tombol "Proses Pembayaran"
    orderButton.addEventListener('click', processOrder);

    // 3. Event delegation untuk tombol di daftar pesanan (+, -, X)
    orderListDiv.addEventListener('click', function(event) {
        const target = event.target.closest('button');
        if (!target) return;

        const itemId = target.dataset.id;
        
        // Mengubah kuantitas
        if (target.classList.contains('btn-qty-change')) {
            const amount = parseInt(target.dataset.amount);
            changeQuantity(itemId, amount);
        }
        
        // Menghapus item
        if (target.classList.contains('btn-remove-item')) {
            removeItem(itemId);
        }
    });

    // 4. Event listener untuk kotak pencarian
    searchInput.addEventListener('keyup', function() {
        const searchTerm = searchInput.value.toLowerCase();
        const allMenuItems = document.querySelectorAll('.menu-grid .col');

        allMenuItems.forEach(function(menuCol) {
            const menuItemCard = menuCol.querySelector('.menu-item');
            const menuName = menuItemCard.dataset.name.toLowerCase();

            if (menuName.includes(searchTerm)) {
                menuCol.style.display = 'block';
            } else {
                menuCol.style.display = 'none';
            }
        });
    });

    // 5. Event listener untuk pilihan metode pembayaran
    paymentMethodRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'Tunai') {
                cashPaymentDiv.style.display = 'block';
                cashAmountInput.value = '';
                calculateChange();
            } else {
                cashPaymentDiv.style.display = 'none';
                updateOrderDisplay(); // Memastikan tombol "Proses Pembayaran" tidak disable jika QRIS
            }
        });
    });

    // 6. Event listener untuk input jumlah tunai
    cashAmountInput.addEventListener('keyup', calculateChange);
    cashAmountInput.addEventListener('change', calculateChange);
    
    // 7. EVENT LISTENER UNTUK TOMBOL CEPAT
    quickCashButtons.forEach(button => {
        button.addEventListener('click', function() {
            const amount = this.dataset.amount;
            cashAmountInput.value = amount;
            calculateChange();
        });
    });

    // Inisialisasi awal
    updateOrderDisplay();
    
    // Pastikan modal muncul jika ada parameter di URL (walaupun fetch lebih baik)
    if (new URLSearchParams(window.location.search).get('status') === 'success' && successModal) {
         successModal.show();
         // Opsional: Hapus parameter URL
         if (window.history.replaceState) {
            const url = new URL(window.location);
            url.searchParams.delete('status');
            window.history.replaceState({}, '', url);
        }
    }

}); // Akhir dari DOMContentLoaded