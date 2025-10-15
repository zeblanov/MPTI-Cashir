/* Custom Styles untuk melengkapi Bootstrap */

/* Membuat tinggi card dan container sama */
.container-fluid, .row, .card {
    height: 100vh;
}
@media (max-width: 992px) {
    .container-fluid, .row, .card {
        height: auto;
    }
}

/* Sidebar Kategori */
.logo {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    background-color: #f0f0f0;
}

.nav-title {
    color: #6c757d; /* Warna text-muted Bootstrap */
    font-size: 0.8rem;
    text-transform: uppercase;
    font-weight: bold;
}

/* Kustomisasi Nav Pills */
.nav-pills .nav-link {
    color: #333;
}

.nav-pills .nav-link.active {
    background-color: #f57c00; /* Warna oranye primer */
}

/* Menu Grid */
.menu-grid {
    overflow-y: auto; /* Memungkinkan scroll jika item banyak */
}

.menu-item {
    cursor: pointer;
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.menu-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
}

.menu-item .card-img-top {
    height: 100px;
    object-fit: cover;
}

.menu-item .fs-sm {
    font-size: 0.9rem;
}

/* Order List */
#order-list {
    overflow-y: auto;
}

.order-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px dotted #ccc;
    font-size: 0.9rem;
}

/* Kustomisasi Order Summary */
.order-summary {
    background-color: #f57c00 !important; /* Timpa warna bg-primary Bootstrap */
}

.order-summary .btn-light {
    color: #f57c00;
}