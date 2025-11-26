/* -------------------------------------------------------
   SMAAKY FRONTEND – FINAL WORKING VERSION
   /beta/assets/js/app.js
------------------------------------------------------- */

// GLOBAL STATE
const state = {
    categories: [],
    products: [],
    extras: [],
    cart: [],
    mode: "delivery", // delivery | pickup
    deliveryFee: 2.50
};

// SIMPLE PRICE FORMAT
function euro(n) {
    return "€ " + n.toFixed(2).replace(".", ",");
}

// LOAD JSON via FETCH
async function fetchJSON(url) {
    const res = await fetch(url, { cache: "no-cache" });
    return await res.json();
}

// INITIAL DATA LOAD
async function loadMenu() {
    try {
        const cats = await fetchJSON("api/categories.php");
        const prods = await fetchJSON("api/products.php");
        const ext = await fetchJSON("api/extras.php");

        state.categories = cats.data;
        state.products = prods.data.filter(p => p.is_active == 1);
        state.extras = ext.data;

        renderMenu();
        updateCartUI();
    }
    catch (e) {
        console.error(e);
        alert("Er ging iets mis bij het laden van het menu.");
    }
}

// RENDER MENU
function renderMenu() {
    const list = document.getElementById("menu-sections");
    list.innerHTML = "";

    state.categories.forEach(cat => {
        const sec = document.createElement("div");
        sec.className = "menu-section";

        sec.innerHTML = `
            <h3 class="menu-section-title">${cat.name}</h3>
            <div class="menu-grid" id="cat-${cat.id}"></div>
        `;

        list.appendChild(sec);

        const grid = sec.querySelector(".menu-grid");

        state.products
            .filter(p => p.category_id == cat.id)
            .forEach(p => {

                const card = document.createElement("div");
                card.className = "product-card";

                const img = p.image || "assets/img/placeholder.png";

                card.innerHTML = `
                    <div class="product-image">
                        <img src="${img}" alt="${p.name}">
                    </div>
                    <div class="product-info">
                        <h4>${p.name}</h4>
                        <p class="product-price">${euro(parseFloat(p.price))}</p>
                        <button class="btn-add" data-id="${p.id}">Toevoegen</button>
                    </div>
                `;

                grid.appendChild(card);
            });
    });

    // Bind to buttons
    document.querySelectorAll(".btn-add").forEach(btn => {
        btn.addEventListener("click", () => addToCart(parseInt(btn.dataset.id)));
    });
}

// ADD TO CART
function addToCart(id) {
    const p = state.products.find(x => x.id === id);
    if (!p) return;

    state.cart.push({
        id: Date.now(),
        product_id: p.id,
        name: p.name,
        qty: 1,
        unit_price: parseFloat(p.price),
        extras: []
    });

    updateCartUI();
}

// CHANGE QTY
function changeQty(itemId, delta) {
    const itm = state.cart.find(i => i.id === itemId);
    if (!itm) return;

    itm.qty += delta;
    if (itm.qty <= 0) {
        state.cart = state.cart.filter(i => i.id !== itemId);
    }

    updateCartUI();
}

// RENDER CART
function updateCartUI() {
    const box = document.getElementById("cart-items");
    box.innerHTML = "";

    if (state.cart.length === 0) {
        box.innerHTML = `<p class="empty-cart">Je winkelmandje is nog leeg.</p>`;
    }

    let subtotal = 0;

    state.cart.forEach(itm => {
        const extrasTotal = itm.extras.reduce((a, b) => a + b.price, 0);
        const line = (itm.unit_price + extrasTotal) * itm.qty;
        subtotal += line;

        const row = document.createElement("div");
        row.className = "cart-row";

        row.innerHTML = `
            <div class="cart-left">
                <b>${itm.name}</b><br>
                ${itm.qty} × ${euro(itm.unit_price)}
            </div>
            <div class="cart-right">
                <button class="qty-btn" onclick="changeQty(${itm.id}, -1)">–</button>
                <span>${itm.qty}</span>
                <button class="qty-btn" onclick="changeQty(${itm.id}, +1)">+</button>
            </div>
        `;

        box.appendChild(row);
    });

    const fee = state.mode === "delivery" ? state.deliveryFee : 0;
    const total = subtotal + fee;

    document.getElementById("cart-subtotal").textContent = euro(subtotal);
    document.getElementById("cart-delivery").textContent = euro(fee);
    document.getElementById("cart-total").textContent = euro(total);
}

// SET DELIVERY MODE
function changeMode(m) {
    state.mode = m;
    updateCartUI();
}

// CHECKOUT
function goCheckout() {
    if (state.cart.length === 0) {
        alert("Je winkelmandje is leeg.");
        return;
    }

    // SAFE CART ENCODE
    const payload = encodeURIComponent(JSON.stringify(state.cart));

    // CORRECT REDIRECT (NO LEADING SLASH!)
    window.location.href = `checkout.php?cart=${payload}`;
}

// PLACE ORDER – FINAL FIXED
async function placeOrder() {

    const cart = state.cart;
    if (cart.length === 0) {
        alert("Je winkelmandje is leeg.");
        return;
    }

    let subtotal = 0;
    const itemsPayload = cart.map(itm => {
        const extras = itm.extras || [];
        const extrasTotal = extras.reduce((a, b) => a + b.price, 0);
        const line = (itm.unit_price + extrasTotal) * itm.qty;
        subtotal += line;

        return {
            product_id: itm.product_id,
            name: itm.name,
            qty: itm.qty,
            unit_price: itm.unit_price,
            extras: extras,
            total: line
        };
    });

    const payload = {
        customer_name: "Online klant",
        phone: "0612345678",
        email: "klant@smaaky.com",
        postal_code: "3068GP",
        street: "Onbekend",
        house_number: "0",
        city: "Rotterdam",
        subtotal: subtotal,
        delivery_fee: state.mode === "delivery" ? state.deliveryFee : 0,
        total: subtotal + (state.mode === "delivery" ? state.deliveryFee : 0),

        // 🔥 REQUIRED FIELD FIX
        delivery_mode: state.mode,

        items: itemsPayload
    };

    try {
        const res = await fetch("api/place_order.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });

        const result = await res.json();

        if (result.status !== "success") {
            alert(result.message || "Er ging iets mis.");
            return;
        }

        // 🔥 REDIRECT FIX (NO LEADING /)
        window.location.href = `order_success.php?id=${result.order_id}`;

    } catch (err) {
        console.error(err);
        alert("Er ging iets mis bij het plaatsen van de bestelling.");
    }
}

// INIT
document.addEventListener("DOMContentLoaded", () => {
    loadMenu();

    document.getElementById("btn-delivery").addEventListener("click", () => changeMode("delivery"));
    document.getElementById("btn-pickup").addEventListener("click", () => changeMode("pickup"));
    document.getElementById("btn-checkout").addEventListener("click", goCheckout);
});