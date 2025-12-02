<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Register - Simple Register</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #1a1a2e;
            color: white;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .header {
            background: #16213e;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-size: 1.3em;
        }
        .header-links a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            background: rgba(255,255,255,0.1);
            border-radius: 5px;
            margin-left: 10px;
        }
        .header-links a:hover {
            background: rgba(255,255,255,0.2);
        }
        .main-container {
            display: flex;
            flex: 1;
            overflow: hidden;
        }
        .products-panel {
            flex: 2;
            padding: 20px;
            overflow-y: auto;
            background: #1a1a2e;
        }
        .bill-panel {
            flex: 1;
            min-width: 300px;
            background: #16213e;
            display: flex;
            flex-direction: column;
            border-left: 1px solid #0f3460;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 15px;
        }
        .product-btn {
            aspect-ratio: 1;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px;
            text-align: center;
            transition: transform 0.1s, box-shadow 0.1s;
            word-break: break-word;
        }
        .product-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }
        .product-btn:active {
            transform: scale(0.95);
        }
        .product-btn .name {
            margin-bottom: 5px;
        }
        .product-btn .price {
            font-size: 0.9em;
            opacity: 0.9;
        }
        .bill-header {
            padding: 20px;
            border-bottom: 1px solid #0f3460;
        }
        .bill-header h2 {
            font-size: 1.2em;
        }
        .bill-items {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
        }
        .bill-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            background: rgba(255,255,255,0.05);
            border-radius: 5px;
            margin-bottom: 8px;
        }
        .bill-item .item-info {
            flex: 1;
        }
        .bill-item .item-name {
            font-weight: 500;
        }
        .bill-item .item-price {
            font-size: 0.9em;
            opacity: 0.7;
        }
        .bill-item .item-qty {
            background: #0f3460;
            padding: 5px 12px;
            border-radius: 15px;
            margin: 0 10px;
        }
        .bill-item .remove-btn {
            background: #e94560;
            border: none;
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2em;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .bill-item .remove-btn:hover {
            background: #c73e54;
        }
        .bill-footer {
            padding: 20px;
            border-top: 1px solid #0f3460;
        }
        .total-display {
            text-align: center;
            margin-bottom: 20px;
        }
        .total-label {
            font-size: 1em;
            opacity: 0.7;
        }
        .total-amount {
            font-size: 2.5em;
            font-weight: 700;
            color: #4ecca3;
        }
        .payment-buttons {
            display: flex;
            gap: 10px;
        }
        .pay-btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 600;
            color: white;
            transition: opacity 0.3s;
        }
        .pay-btn:hover {
            opacity: 0.9;
        }
        .pay-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .pay-btn.cash {
            background: #4ecca3;
        }
        .pay-btn.card {
            background: #007bff;
        }
        .clear-btn {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            background: #e94560;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            font-size: 1em;
        }
        .clear-btn:hover {
            background: #c73e54;
        }
        .no-products {
            text-align: center;
            padding: 40px;
            opacity: 0.6;
        }
        .no-products a {
            color: #4ecca3;
        }
        .empty-bill {
            text-align: center;
            padding: 40px 20px;
            opacity: 0.5;
        }
        .layout-controls {
            padding: 10px 20px;
            background: #0f3460;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .layout-controls label {
            font-size: 0.9em;
        }
        .layout-controls input {
            width: 60px;
            padding: 5px;
            border: none;
            border-radius: 3px;
            margin-left: 10px;
        }
        .layout-controls button {
            padding: 5px 15px;
            background: #4ecca3;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            color: #1a1a2e;
            font-weight: 600;
        }
        .success-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }
        .success-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        .success-message {
            background: #4ecca3;
            color: #1a1a2e;
            padding: 40px 60px;
            border-radius: 15px;
            text-align: center;
            transform: scale(0.8);
            transition: transform 0.3s;
        }
        .success-overlay.show .success-message {
            transform: scale(1);
        }
        .success-message h2 {
            font-size: 2em;
            margin-bottom: 10px;
        }
        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
            }
            .bill-panel {
                min-width: 100%;
                max-height: 50vh;
            }
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📋 Cash Register</h1>
        <div class="header-links">
            <a href="admin.php">⚙️ Admin</a>
            <a href="index.php">🏠 Home</a>
        </div>
    </div>
    
    <div class="layout-controls">
        <div>
            <label>Button Size: <input type="number" id="btnSize" value="120" min="80" max="200">px</label>
        </div>
        <button onclick="saveLayout()">💾 Save Layout</button>
    </div>
    
    <div class="main-container">
        <div class="products-panel">
            <?php if (empty($articles)): ?>
            <div class="no-products">
                <p>No articles configured yet.</p>
                <p><a href="admin.php">Go to Admin Panel</a> to add articles.</p>
            </div>
            <?php else: ?>
            <div class="products-grid" id="productsGrid">
                <?php foreach ($articles as $article): ?>
                <button class="product-btn" 
                        style="background-color: <?php echo View::escape($article['color'] ?? '#007bff'); ?>"
                        data-id="<?php echo View::escape($article['id']); ?>"
                        data-name="<?php echo View::escape($article['name']); ?>"
                        data-price="<?php echo View::escape($article['price']); ?>"
                        onclick="addToCart(this)">
                    <span class="name"><?php echo View::escape($article['name']); ?></span>
                    <span class="price"><?php echo number_format($article['price'], 2); ?> €</span>
                </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="bill-panel">
            <div class="bill-header">
                <h2>Current Bill</h2>
            </div>
            <div class="bill-items" id="billItems">
                <div class="empty-bill">
                    <p>No items yet</p>
                    <p>Click a product to add it</p>
                </div>
            </div>
            <div class="bill-footer">
                <div class="total-display">
                    <div class="total-label">Total</div>
                    <div class="total-amount" id="totalAmount">0.00 €</div>
                </div>
                <div class="payment-buttons">
                    <button class="pay-btn cash" onclick="pay('cash')" id="payCashBtn" disabled>💵 Cash</button>
                    <button class="pay-btn card" onclick="pay('card')" id="payCardBtn" disabled>💳 Card</button>
                </div>
                <button class="clear-btn" onclick="clearCart()">🗑️ Clear Bill</button>
            </div>
        </div>
    </div>
    
    <div class="success-overlay" id="successOverlay">
        <div class="success-message">
            <h2>✓ Payment Complete!</h2>
            <p id="successDetails"></p>
        </div>
    </div>
    
    <script>
        // Cart state
        let cart = {};
        
        // Articles data from PHP
        const articles = <?php echo $articlesJson; ?>;
        
        // Load layout from localStorage
        function loadLayout() {
            const savedSize = localStorage.getItem('register_btn_size');
            if (savedSize) {
                document.getElementById('btnSize').value = savedSize;
                applyLayout(savedSize);
            }
        }
        
        // Apply layout settings
        function applyLayout(size) {
            const grid = document.getElementById('productsGrid');
            if (grid) {
                grid.style.gridTemplateColumns = `repeat(auto-fill, minmax(${size}px, 1fr))`;
            }
        }
        
        // Save layout to localStorage
        function saveLayout() {
            const size = document.getElementById('btnSize').value;
            localStorage.setItem('register_btn_size', size);
            applyLayout(size);
        }
        
        // Add item to cart
        function addToCart(btn) {
            const id = btn.dataset.id;
            const name = btn.dataset.name;
            const price = parseFloat(btn.dataset.price);
            
            if (cart[id]) {
                cart[id].qty++;
            } else {
                cart[id] = { id, name, price, qty: 1 };
            }
            
            updateCartDisplay();
        }
        
        // Remove item from cart
        function removeFromCart(id) {
            if (cart[id]) {
                cart[id].qty--;
                if (cart[id].qty <= 0) {
                    delete cart[id];
                }
            }
            updateCartDisplay();
        }
        
        // Clear entire cart
        function clearCart() {
            cart = {};
            updateCartDisplay();
        }
        
        // Update cart display
        function updateCartDisplay() {
            const billItems = document.getElementById('billItems');
            const totalEl = document.getElementById('totalAmount');
            const payCashBtn = document.getElementById('payCashBtn');
            const payCardBtn = document.getElementById('payCardBtn');
            
            const items = Object.values(cart);
            
            if (items.length === 0) {
                billItems.innerHTML = `
                    <div class="empty-bill">
                        <p>No items yet</p>
                        <p>Click a product to add it</p>
                    </div>
                `;
                totalEl.textContent = '0.00 €';
                payCashBtn.disabled = true;
                payCardBtn.disabled = true;
                return;
            }
            
            let html = '';
            let total = 0;
            
            items.forEach(item => {
                const itemTotal = item.price * item.qty;
                total += itemTotal;
                html += `
                    <div class="bill-item">
                        <div class="item-info">
                            <div class="item-name">${escapeHtml(item.name)}</div>
                            <div class="item-price">${item.price.toFixed(2)} € each</div>
                        </div>
                        <span class="item-qty">×${item.qty}</span>
                        <button class="remove-btn" onclick="removeFromCart('${item.id}')">−</button>
                    </div>
                `;
            });
            
            billItems.innerHTML = html;
            totalEl.textContent = total.toFixed(2) + ' €';
            payCashBtn.disabled = false;
            payCardBtn.disabled = false;
        }
        
        // Process payment
        function pay(method) {
            const items = Object.values(cart);
            if (items.length === 0) return;
            
            const total = items.reduce((sum, item) => sum + (item.price * item.qty), 0);
            
            // Send to server
            fetch('api/payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    items: items,
                    total: total,
                    method: method
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess(method, total);
                    clearCart();
                } else {
                    alert('Payment failed: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(err => {
                console.error('Payment error:', err);
                alert('Payment failed. Please try again.');
            });
        }
        
        // Show success overlay
        function showSuccess(method, total) {
            const overlay = document.getElementById('successOverlay');
            const details = document.getElementById('successDetails');
            details.textContent = `${total.toFixed(2)} € paid by ${method}`;
            overlay.classList.add('show');
            
            setTimeout(() => {
                overlay.classList.remove('show');
            }, 2000);
        }
        
        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Listen for size input changes
        document.getElementById('btnSize').addEventListener('input', function() {
            applyLayout(this.value);
        });
        
        // Initialize
        loadLayout();
    </script>
</body>
</html>
