<!DOCTYPE html>
<html lang="<?php echo $lang->getLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang->get('register_title'); ?></title>
    <link rel="icon" type="image/svg+xml" href="favicon/favicon.svg">
    <link rel="icon" type="image/x-icon" href="favicon/favicon.ico">
        <link rel="stylesheet" href="views/common.css">
        <style>
            html, body {
                overscroll-behavior-y: contain;
                touch-action: manipulation;
            }
        </style>
    <link rel="stylesheet" href="views/register.css">
    <script src="views/js/interact.min.js"></script>
    <script>
        const articles = <?php echo $articlesJson; ?>;
        const translations = {
            payment_failed: "<?php echo addslashes($__('payment_failed')); ?>",
            unknown_error: "<?php echo addslashes($__('unknown_error')); ?>",
            payment_failed_try_again: "<?php echo addslashes($__('payment_failed_try_again')); ?>"
        };
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
        <h1>📋 <?php echo $__('register'); ?></h1>
        <div class="header-links">
                        <a href="reports.php">📊 <?php echo $__('reports'); ?></a>
                        <a href="admin.php">⚙️ <?php echo $__('admin'); ?></a>
                        <a href="index.php">🏠 <?php echo $__('home'); ?></a>
                        <?php include '_language_selector.php'; ?>
                        <span style="margin-left:16px; display:inline-flex; align-items:center; gap:6px;">
                            <select id="layoutSelect"></select>
                            <button id="saveLayoutBtn" style="padding:2px 8px;">💾</button>
                        </span>
        </div>
    </div>

    <div class="layout-controls">
        <div>
            <button id="editModeBtn" onclick="toggleEditMode()">✏️ <?php echo $__('edit_layout'); ?></button>
        </div>
    </div>

    <div class="main-container">
        <div class="products-panel">
            <?php if (empty($articles)): ?>
                <div class="no-products">
                    <p><?php echo $__('no_articles_configured'); ?></p>
                    <p><a href="admin.php"><?php echo $__('go_to_admin_panel'); ?></a> <?php echo $__('to_add_articles'); ?></p>
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
                <h2><?php echo $__('current_bill'); ?></h2>
            </div>
            <div class="bill-items" id="billItems">
                <div class="empty-bill">
                    <p><?php echo $__('no_items_yet'); ?></p>
                    <p><?php echo $__('click_product_to_add'); ?></p>
                </div>
            </div>
            <div class="bill-footer">
                <div class="total-display">
                    <div class="total-label"><?php echo $__('total'); ?></div>
                    <div class="total-amount" id="totalAmount">0.00 €</div>
                </div>
                <div class="payment-buttons">
                    <button class="pay-btn cash" onclick="pay('cash')" id="payCashBtn" disabled>💵 <?php echo $__('cash'); ?></button>
                    <button class="pay-btn card" onclick="pay('card')" id="payCardBtn" disabled>💳 <?php echo $__('card'); ?></button>
                </div>
                <button class="clear-btn" onclick="clearCart()">🗑️ <?php echo $lang->get('clear_bill'); ?></button>
            </div>
        </div>

        <div class="edit-panel" id="editPanel">
            <div class="bill-header">
                <h2><?php echo $__('available_products'); ?></h2>
            </div>
            <div class="add-products-grid" id="editAvailableProducts">
                <!-- Available products will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <div class="success-overlay" id="successOverlay">
        <div class="success-message">
            <h2>✓ <?php echo $__('payment_complete'); ?></h2>
            <p id="successDetails"></p>
        </div>
    </div>
</body>
</html>