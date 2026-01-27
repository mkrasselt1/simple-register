<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Register - Simple Register</title>
        <link rel="stylesheet" href="views/common.css">
        <style>
            html, body {
                overscroll-behavior-y: contain;
                touch-action: manipulation;
            }
        </style>
    <link rel="stylesheet" href="views/register.css">
    <script src="https://cdn.jsdelivr.net/npm/interactjs@1.10.17/dist/interact.min.js"></script>
    <script>
        const articles = <?php echo $articlesJson; ?>;
    </script>
    <script src="views/register.js"></script>
    <?php require_once __DIR__ . '/_color_utils.php'; ?>
</head>
<body>
        <script>
            // Versuche Pull-to-Refresh auf Mobilgeräten zu verhindern
            window.addEventListener('touchmove', function(e) {
                if (window.scrollY === 0 && e.touches[0].clientY > 0) {
                    e.preventDefault();
                }
            }, { passive: false });
        </script>
        <div class="header">
        <h1>📋 Cash Register</h1>
        <div class="header-links">
                        <a href="reports.php">📊 Reports</a>
                        <a href="admin.php">⚙️ Admin</a>
                        <a href="index.php">🏠 Home</a>
                        <span style="margin-left:16px; display:inline-flex; align-items:center; gap:6px;">
                            <select id="layoutSelect"></select>
                            <button id="saveLayoutBtn" style="padding:2px 8px;">💾</button>
                        </span>
        </div>
    </div>

    <div class="layout-controls">
        <div>
            <button id="editModeBtn" onclick="toggleEditMode()">✏️ Edit Layout</button>
        </div>
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
                            style="background-color: <?php echo View::escape($article['color'] ?? '#007bff'); ?>; color: <?php echo View::escape($article['textColor'] ?? '#fff'); ?>;"
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

        <div class="bill-panel" id="billPanel">
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

        <div class="edit-panel" id="editPanel">
            <div class="bill-header">
                <h2>Available Products</h2>
            </div>
            <div class="add-products-grid" id="editAvailableProducts">
                <!-- Available products will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <div class="success-overlay" id="successOverlay">
        <div class="success-message">
            <h2>✓ Payment Complete!</h2>
            <p id="successDetails"></p>
        </div>
    </div>
</body>
</html>