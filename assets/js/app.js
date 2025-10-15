document.addEventListener('DOMContentLoaded', function() {

    // VARIABEL GLOBAL
    let currentOrder = []; // Menyimpan item pesanan saat ini
    const menuItems = document.querySelectorAll('.menu-item');
    const orderListDiv = document.getElementById('order-list');
    const totalPriceSpan = document.getElementById('total-price');
    const orderButton = document.getElementById('btn-order');

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

    /** Memperbarui tampilan daftar pesanan dan total harga. */
    function updateOrderDisplay() {
        orderListDiv.innerHTML = '';
        let totalPrice = 0;

        if (currentOrder.length === 0) {
            orderListDiv.innerHTML = '<p class="placeholder">Belum ada pesanan...</p>';
        } else {
            currentOrder.forEach(item => {
                const subtotal = item.price * item.qty;
                totalPrice += subtotal;

                const itemDiv = document.createElement('div');
                itemDiv.className = 'order-item';
                itemDiv.innerHTML = `
                    <span>${item.name} (x${item.qty})</span>
                    <span>Rp ${formatRupiah(subtotal)}</span>`;
                orderListDiv.appendChild(itemDiv);
            });
        }
        totalPriceSpan.innerText = `Rp ${formatRupiah(totalPrice)}`;
    }

    /** Mengirim data pesanan ke backend. */
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
                alert('Pesanan berhasil diproses!');
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

    // FUNGSI BANTU
    /** Mengubah angka menjadi format Rupiah (15000 -> 15.000). */
    function formatRupiah(angka) {
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // EVENT LISTENERS
    // Menambahkan event klik untuk setiap item menu
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            const id = parseInt(this.dataset.id);
            const name = this.dataset.name;
            const price = parseInt(this.dataset.price);
            addItem(id, name, price);
        });
    });

    // Menambahkan event klik untuk tombol proses pesanan
    orderButton.addEventListener('click', processOrder);
});