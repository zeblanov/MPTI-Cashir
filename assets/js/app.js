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
    const successModal = new bootstrap.Modal(successModalEl);

    // FUNGSI UTAMA

    /** Menambahkan item ke pesanan atau memperbarui jumlahnya. */
    function addItem(id, name, price) {
        const existingItem = currentOrder.find(item => item.id === id);
        if (existingItem) {
            existingItem.qty += 1;
        } else {
            currentOrder.push({ id, name, price, qty: 1 });
        }
        updateOrderDisplay();
    }

    /** [UPDATED] Memperbarui tampilan daftar pesanan dengan ikon edit dan hapus silang. */
    function updateOrderDisplay() {
        orderListDiv.innerHTML = '';
        let totalPrice = 0;
        let totalItems = 0;

        if (currentOrder.length === 0) {
            orderListDiv.innerHTML = '<p class="text-center text-muted mt-4">Silahkan Pilih Menu...</p>';
        } else {
            currentOrder.forEach(item => {
                const subtotal = item.price * item.qty;
                totalPrice += subtotal;
                totalItems += item.qty;

                const itemDiv = document.createElement('div');
                itemDiv.className = 'order-item';
                itemDiv.innerHTML = `
                    <div class="item-info">
                        <span class="item-name">${item.name}</span>
                        <span class="item-price">Rp ${formatRupiah(item.price)}</span>
                    </div>
                    <div class="item-controls">
                        <div class="btn-group" role="group">
                            <button class="btn btn-outline-secondary btn-qty-change" data-id="${item.id}" data-amount="-1">
                                <i class="bi bi-dash-lg"></i>
                            </button>
                            <button class="btn btn-outline-secondary btn-qty-display" disabled>${item.qty}</button>
                            <button class="btn btn-outline-secondary btn-qty-change" data-id="${item.id}" data-amount="1">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                        <button class="btn btn-outline-danger btn-remove-item" data-id="${item.id}">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                `;
                orderListDiv.appendChild(itemDiv);
            });
        }
        totalPriceSpan.innerText = `Rp ${formatRupiah(totalPrice)}`;
        itemCountSpan.innerText = `${totalItems} items`;
    }
    
    /** [NEW] Mengubah jumlah (quantity) dari sebuah item. */
    function changeQuantity(itemId, amount) {
        const item = currentOrder.find(i => String(i.id) === String(itemId));
        if (item) {
            item.qty += amount;
            if (item.qty <= 0) {
                removeItem(itemId); // Hapus jika jumlah 0 atau kurang
            } else {
                updateOrderDisplay();
            }
        }
    }

    /** [NEW] Menghapus sebuah item dari daftar pesanan. */
    function removeItem(itemId) {
        currentOrder = currentOrder.filter(i => String(i.id) !== String(itemId));
        updateOrderDisplay();
    }
    
    /** [UPDATED] Mengirim data pesanan ke backend dan menampilkan modal. */
    async function processOrder() {
        if (currentOrder.length === 0) {
            alert('Keranjang masih kosong!');
            return;
        }
        try {
            const response = await fetch('proses_pesanan.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(currentOrder)
            });
            const result = await response.json();
            
            if (result.success) {
                successModal.show(); // Tampilkan modal sukses
                currentOrder = [];
                updateOrderDisplay();
            } else {
                alert('Gagal memproses pesanan: ' + (result.message || 'Error tidak diketahui'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan. Pastikan file proses_pesanan.php sudah ada dan benar.');
        }
    }

    /** Mengubah angka menjadi format Rupiah. */
    function formatRupiah(angka) {
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
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

    // 2. Event klik untuk tombol "Order"
    orderButton.addEventListener('click', processOrder);

    // 3. Event delegation untuk tombol di daftar pesanan (+, -, X)
    orderListDiv.addEventListener('click', function(event) {
        const target = event.target.closest('button');
        if (!target) return;

        const itemId = target.dataset.id;

        if (target.classList.contains('btn-qty-change')) {
            const amount = parseInt(target.dataset.amount);
            changeQuantity(itemId, amount);
        }

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
});