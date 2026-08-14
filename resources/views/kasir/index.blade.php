@extends('layouts.app')

@section('title', 'Transaksi Kasir POS & Rekomendasi Apriori Real-Time')

@section('content')
<div class="space-y-6">

    <!-- Header POS -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-cash-register text-emerald-600"></i> Kasir Penjualan (POS)
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Sistem Kasir Terintegrasi Rekomendasi Produk Apriori Real-Time - Toko Serba 123 Toy Store</p>
        </div>
        
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200 text-xs">
                <span class="font-bold text-slate-500">Shift Kerja:</span>
                <select id="shift_select" class="bg-transparent font-extrabold text-slate-800 focus:outline-none cursor-pointer">
                    <option value="pagi">Shift Pagi</option>
                    <option value="siang">Shift Siang</option>
                    <option value="sore">Shift Sore</option>
                </select>
            </div>
            <div class="text-xs text-slate-500 font-bold bg-emerald-50 text-emerald-800 px-3 py-1.5 rounded-xl border border-emerald-200">
                <i class="fa-solid fa-user text-emerald-600 mr-1"></i> {{ auth()->user()->nama }}
            </div>
        </div>
    </div>

    <!-- Main Grid: Products (Left) + Shopping Cart & Realtime Recommendation (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- LEFT COLUMN: Product Catalog & Search (7 Cols) -->
        <div class="lg:col-span-7 space-y-4">
            
            <!-- Catalog Filter & Search -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-grow">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input 
                            type="text" 
                            id="search_input" 
                            onkeyup="filterProducts()"
                            placeholder="Cari nama mainan atau ID..." 
                            class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                    </div>
                    
                    <select id="category_filter" onchange="filterProducts()" class="px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Semua Kategori</option>
                        @php
                            $kategoriList = $produks->pluck('kategori')->unique();
                        @endphp
                        @foreach($kategoriList as $kat)
                            <option value="{{ $kat }}">{{ $kat }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 max-h-[560px] overflow-y-auto pr-1 scrollbar-thin" id="product_grid">
                @foreach($produks as $p)
                    <div 
                        class="product-card bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs hover:border-emerald-400 hover:shadow-md transition-all cursor-pointer flex flex-col justify-between group"
                        data-id="{{ $p->id }}"
                        data-code="{{ $p->id_produk }}"
                        data-name="{{ $p->nama_produk }}"
                        data-category="{{ $p->kategori }}"
                        data-price="{{ $p->harga }}"
                        data-stock="{{ $p->stok }}"
                        onclick="addToCart({{ $p->id }}, '{{ addslashes($p->nama_produk) }}', {{ $p->harga }}, {{ $p->stok }}, '{{ $p->id_produk }}')">
                        
                        <div>
                            <div class="flex items-center justify-between text-[10px] font-bold text-slate-400 mb-1">
                                <span class="font-mono bg-slate-100 px-1.5 py-0.5 rounded">{{ $p->id_produk }}</span>
                                <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700">Stok: {{ $p->stok }}</span>
                            </div>
                            <h4 class="font-extrabold text-xs text-slate-800 group-hover:text-emerald-600 transition-colors line-clamp-2 leading-snug">
                                {{ $p->nama_produk }}
                            </h4>
                            <span class="inline-block text-[10px] font-semibold text-slate-400 mt-1">
                                {{ $p->kategori }}
                            </span>
                        </div>

                        <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between">
                            <span class="font-black text-xs text-emerald-700">Rp {{ number_format($p->harga, 0, ',', '.') }}</span>
                            <button class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 group-hover:bg-emerald-600 group-hover:text-white flex items-center justify-center text-xs transition-colors">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- RIGHT COLUMN: Cart & Real-Time Recommendations (5 Cols) -->
        <div class="lg:col-span-5 space-y-4">

            <!-- REAL-TIME RECOMMENDATION CARD (APRIORI ENGINE) -->
            <div id="recommendation_box" class="bg-gradient-to-r from-purple-900 to-indigo-900 text-white rounded-2xl p-4 shadow-md hidden">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-purple-500/30 text-purple-200 flex items-center justify-center text-xs font-bold animate-pulse">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                        </span>
                        <h3 class="text-xs font-extrabold tracking-wide uppercase text-purple-200">
                            Rekomendasi Produk Pelengkap (Apriori)
                        </h3>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 bg-purple-500/30 text-purple-200 rounded-full border border-purple-400/30">
                        Real-Time
                    </span>
                </div>
                <p class="text-[11px] text-purple-200/80 mb-3">
                    Pelanggan yang membeli produk di keranjang cenderung membeli produk berikut:
                </p>

                <div id="recommendations_list" class="space-y-2 max-h-48 overflow-y-auto">
                    <!-- Populated dynamically via AJAX -->
                </div>
            </div>

            <!-- CART CARD -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex flex-col justify-between min-h-[480px]">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                        <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-cart-shopping text-emerald-600"></i> Keranjang Transaksi
                        </h3>
                        <button type="button" onclick="clearCart()" class="text-xs text-rose-500 hover:text-rose-700 font-bold flex items-center gap-1">
                            <i class="fa-solid fa-trash-can"></i> Kosongkan
                        </button>
                    </div>

                    <!-- Cart Item Table/List -->
                    <div id="cart_items_container" class="space-y-2 max-h-72 overflow-y-auto pr-1">
                        <div id="empty_cart_msg" class="text-center py-12 text-slate-400">
                            <i class="fa-solid fa-basket-shopping text-4xl mb-2 text-slate-300 block"></i>
                            <p class="text-xs font-bold text-slate-500">Keranjang Masih Kosong</p>
                            <p class="text-[11px] text-slate-400">Pilih produk mainan di sebelah kiri untuk memulai transaksi.</p>
                        </div>
                    </div>
                </div>

                <!-- Total & Payment Checkout Footer -->
                <div class="mt-4 pt-4 border-t border-slate-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Pembayaran</span>
                        <span id="cart_total_display" class="text-2xl font-black text-emerald-700">Rp 0</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Nominal Bayar (Rp)</label>
                            <input 
                                type="number" 
                                id="bayar_input" 
                                oninput="calculateChange()"
                                placeholder="0" 
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Kembalian (Rp)</label>
                            <input 
                                type="text" 
                                id="kembalian_display" 
                                readonly 
                                value="Rp 0" 
                                class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-xl text-xs font-extrabold text-slate-700 cursor-not-allowed">
                        </div>
                    </div>

                    <button 
                        type="button" 
                        id="btn_submit_trx" 
                        onclick="processTransaction()" 
                        disabled
                        class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 text-white font-extrabold text-sm rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer disabled:cursor-not-allowed">
                        <i class="fa-solid fa-check-double"></i> Simpan & Cetak Transaksi
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div id="receipt_modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl space-y-4">
        <div class="text-center border-b border-dashed border-slate-300 pb-3">
            <h3 class="font-black text-lg text-slate-800 uppercase tracking-tight">SERBA 123 TOY STORE</h3>
            <p class="text-[11px] text-slate-500">Jatinangor, Sumedang - Jawa Barat</p>
            <p class="text-[10px] text-slate-400 font-mono mt-1" id="receipt_trx_code">TRX-000</p>
        </div>

        <div class="space-y-2 text-xs font-mono text-slate-700" id="receipt_items_list">
            <!-- Populated on completion -->
        </div>

        <div class="border-t border-dashed border-slate-300 pt-3 space-y-1 text-xs font-mono">
            <div class="flex justify-between font-bold text-slate-800">
                <span>TOTAL:</span>
                <span id="receipt_total">Rp 0</span>
            </div>
            <div class="flex justify-between text-slate-600">
                <span>BAYAR:</span>
                <span id="receipt_bayar">Rp 0</span>
            </div>
            <div class="flex justify-between text-slate-600">
                <span>KEMBALI:</span>
                <span id="receipt_kembalian">Rp 0</span>
            </div>
        </div>

        <div class="text-center text-[10px] text-slate-400 font-mono pt-2 border-t border-slate-100">
            <p>Terima Kasih Telah Berbelanja!</p>
            <p>Mainan Edukatif & Berkualitas Anak</p>
        </div>

        <div class="flex items-center gap-2 pt-2">
            <button type="button" onclick="window.print()" class="flex-1 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-print"></i> Cetak Struk
            </button>
            <button type="button" onclick="closeReceiptModal()" class="flex-1 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl">
                Selesai / Transaksi Baru
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
let cart = [];

function filterProducts() {
    const query = document.getElementById('search_input').value.toLowerCase();
    const category = document.getElementById('category_filter').value.toLowerCase();
    const cards = document.querySelectorAll('.product-card');

    cards.forEach(card => {
        const name = card.dataset.name.toLowerCase();
        const code = card.dataset.code.toLowerCase();
        const cat = card.dataset.category.toLowerCase();

        const matchQuery = name.includes(query) || code.includes(query);
        const matchCat = category === '' || cat === category;

        if (matchQuery && matchCat) {
            card.classList.remove('hidden');
        } else {
            card.classList.add('hidden');
        }
    });
}

function addToCart(id, name, price, stock, code) {
    const existingIndex = cart.findIndex(item => item.id === id);

    if (existingIndex > -1) {
        if (cart[existingIndex].jumlah + 1 > stock) {
            alert(`Stok produk "${name}" tidak mencukupi (Sisa: ${stock}).`);
            return;
        }
        cart[existingIndex].jumlah += 1;
    } else {
        if (stock < 1) {
            alert(`Stok produk "${name}" habis.`);
            return;
        }
        cart.push({ id, name, price, stock, code, jumlah: 1 });
    }

    updateCartUI();
    fetchRealtimeRecommendations();
}

function updateCartQuantity(id, change) {
    const index = cart.findIndex(item => item.id === id);
    if (index > -1) {
        const newQty = cart[index].jumlah + change;
        if (newQty <= 0) {
            cart.splice(index, 1);
        } else if (newQty > cart[index].stock) {
            alert(`Stok produk tidak mencukupi (Sisa: ${cart[index].stock}).`);
        } else {
            cart[index].jumlah = newQty;
        }
    }
    updateCartUI();
    fetchRealtimeRecommendations();
}

function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    updateCartUI();
    fetchRealtimeRecommendations();
}

function clearCart() {
    cart = [];
    updateCartUI();
    fetchRealtimeRecommendations();
}

function getCartTotal() {
    return cart.reduce((sum, item) => sum + (item.price * item.jumlah), 0);
}

function updateCartUI() {
    const container = document.getElementById('cart_items_container');
    const totalDisplay = document.getElementById('cart_total_display');
    const btnSubmit = document.getElementById('btn_submit_trx');

    if (cart.length === 0) {
        container.innerHTML = `
            <div id="empty_cart_msg" class="text-center py-12 text-slate-400">
                <i class="fa-solid fa-basket-shopping text-4xl mb-2 text-slate-300 block"></i>
                <p class="text-xs font-bold text-slate-500">Keranjang Masih Kosong</p>
                <p class="text-[11px] text-slate-400">Pilih produk mainan di sebelah kiri untuk memulai transaksi.</p>
            </div>
        `;
        totalDisplay.innerText = 'Rp 0';
        btnSubmit.disabled = true;
        calculateChange();
        return;
    }

    let html = '';
    cart.forEach(item => {
        const subtotal = item.price * item.jumlah;
        html += `
            <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center justify-between gap-2">
                <div class="flex-grow min-w-0">
                    <h5 class="font-extrabold text-xs text-slate-800 truncate">${item.name}</h5>
                    <span class="text-[11px] text-slate-500 font-medium">Rp ${item.price.toLocaleString('id-ID')} x ${item.jumlah}</span>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <button type="button" onclick="updateCartQuantity(${item.id}, -1)" class="w-6 h-6 rounded-md bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold">-</button>
                    <span class="w-6 text-center font-extrabold text-xs text-slate-800">${item.jumlah}</span>
                    <button type="button" onclick="updateCartQuantity(${item.id}, 1)" class="w-6 h-6 rounded-md bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold">+</button>
                    <button type="button" onclick="removeFromCart(${item.id})" class="w-6 h-6 rounded-md bg-rose-100 hover:bg-rose-200 text-rose-600 text-xs font-bold ml-1">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
    const total = getCartTotal();
    totalDisplay.innerText = 'Rp ' + total.toLocaleString('id-ID');
    btnSubmit.disabled = false;
    calculateChange();
}

function calculateChange() {
    const total = getCartTotal();
    const bayar = parseFloat(document.getElementById('bayar_input').value) || 0;
    const kembalianDisplay = document.getElementById('kembalian_display');

    if (bayar >= total) {
        const kembalian = bayar - total;
        kembalianDisplay.value = 'Rp ' + kembalian.toLocaleString('id-ID');
    } else {
        kembalianDisplay.value = 'Rp 0 (Kurang)';
    }
}

function fetchRealtimeRecommendations() {
    const box = document.getElementById('recommendation_box');
    const list = document.getElementById('recommendations_list');

    if (cart.length === 0) {
        box.classList.add('hidden');
        list.innerHTML = '';
        return;
    }

    const productNames = cart.map(item => item.name);

    fetch("{{ url('/kasir/recommendations') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            product_names: productNames
        })
    })
    .then(async response => {
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || `HTTP Error ${response.status}`);
        }

        return data;
    })
    .then(data => {
        if (data.recommendations && data.recommendations.length > 0) {
            let html = '';

            data.recommendations.forEach(rec => {
                html += `
                    <div class="p-2.5 bg-white/10 backdrop-blur-md rounded-xl border border-purple-400/30 flex items-center justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="font-extrabold text-xs text-white">
                                    ${rec.nama_produk}
                                </span>

                                <span class="px-1.5 py-0.2 bg-emerald-500/80 text-white text-[9px] font-bold rounded">
                                    Conf: ${rec.confidence}%
                                </span>
                            </div>

                            <span class="text-[10px] text-purple-200 block">
                                Karena memilih "${rec.antecedent}" •
                                Rp ${rec.harga.toLocaleString('id-ID')}
                            </span>
                        </div>

                        <button
                            type="button"
                            onclick="addToCart(
                                ${rec.id},
                                '${rec.nama_produk.replace(/'/g, "\\'")}',
                                ${rec.harga},
                                ${rec.stok},
                                '${rec.id_produk}'
                            )"
                            class="px-2.5 py-1 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold rounded-lg">
                            <i class="fa-solid fa-cart-plus text-[10px]"></i>
                            Tambah
                        </button>
                    </div>
                `;
            });

            list.innerHTML = html;
            box.classList.remove('hidden');
        } else {
            box.classList.add('hidden');
            list.innerHTML = '';
        }
    })
    .catch(error => {
        console.error('Error fetching recommendations:', error);
        box.classList.add('hidden');
        list.innerHTML = '';
    });
}

function processTransaction() {
    if (cart.length === 0) return;

    const total = getCartTotal();
    const bayar = parseFloat(document.getElementById('bayar_input').value) || 0;
    const shift = document.getElementById('shift_select').value;

    if (bayar < total) {
        alert(`Jumlah pembayaran kurang dari total belanja (Total: Rp ${total.toLocaleString('id-ID')}).`);
        return;
    }

    const payload = {
        items: cart.map(i => ({ produk_id: i.id, jumlah: i.jumlah })),
        shift: shift,
        bayar: bayar
    };

    const btnSubmit = document.getElementById('btn_submit_trx');
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Memproses...`;

    fetch("{{ route('kasir.store') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showReceiptModal(data.transaksi_id, total, bayar);
            clearCart();
            document.getElementById('bayar_input').value = '';
            calculateChange();
        } else {
            alert(data.message || "Gagal memproses transaksi.");
        }
    })
    .catch(err => {
        alert("Terjadi kesalahan sistem.");
        console.error(err);
    })
    .finally(() => {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = `<i class="fa-solid fa-check-double"></i> Simpan & Cetak Transaksi`;
    });
}

function showReceiptModal(trxId, total, bayar) {
    const modal = document.getElementById('receipt_modal');
    document.getElementById('receipt_trx_code').innerText = "ID: " + trxId;

    let itemsHtml = '';
    cart.forEach(i => {
        itemsHtml += `
            <div class="flex justify-between">
                <span>${i.name} x${i.jumlah}</span>
                <span>Rp ${(i.price * i.jumlah).toLocaleString('id-ID')}</span>
            </div>
        `;
    });
    document.getElementById('receipt_items_list').innerHTML = itemsHtml;
    document.getElementById('receipt_total').innerText = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('receipt_bayar').innerText = 'Rp ' + bayar.toLocaleString('id-ID');
    document.getElementById('receipt_kembalian').innerText = 'Rp ' + (bayar - total).toLocaleString('id-ID');

    modal.classList.remove('hidden');
}

function closeReceiptModal() {
    document.getElementById('receipt_modal').classList.add('hidden');
    window.location.reload();
}
</script>
@endpush
@endsection
