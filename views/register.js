// register.js
// Ausgelagertes JavaScript aus register.php

// Cart state
let cart = {};

// Edit mode state
let isEditMode = false;
let buttonPositions = {}; // id: {x, y, width, height}

// Load layout from localStorage
function loadLayout() {
  // Load button positions
  const savedPositions = localStorage.getItem("register_button_positions");
  if (savedPositions) {
    buttonPositions = JSON.parse(savedPositions);
  }

  // Load visible products order
  const savedOrder = localStorage.getItem("register_visible_products");
  let visibleIds = savedOrder ? JSON.parse(savedOrder) : [];

  // Snap all stored positions to whole cells (repairs older layouts)
  visibleIds.forEach((id) => {
    if (buttonPositions[id]) buttonPositions[id] = normalizePos(buttonPositions[id]);
  });
  localStorage.setItem("register_visible_products", JSON.stringify(visibleIds));

  renderProductsGrid(visibleIds);
}

// Render products grid with optional order
function renderProductsGrid(visibleIds = null) {
  const grid = document.getElementById("productsGrid");
  if (!grid) return;

  if (visibleIds === null) {
    const savedOrder = localStorage.getItem("register_visible_products");
    visibleIds = savedOrder ? JSON.parse(savedOrder) : [];
  }

  let visibleArticles;
  visibleArticles = articles.filter((article) =>
    visibleIds.includes(article.id),
  );
  // Sort by the order in visibleIds
  visibleArticles.sort(
    (a, b) => visibleIds.indexOf(a.id) - visibleIds.indexOf(b.id),
  );

  grid.innerHTML = "";
  visibleArticles.forEach((article) => {
    const btn = document.createElement("button");
    btn.className = "product-btn";
    btn.style.backgroundColor = article.color || "#007bff";
    btn.style.color = article.textColor || "#fff";
    btn.dataset.id = article.id;
    btn.dataset.name = article.name;
    btn.dataset.price = article.price;
    btn.onclick = isEditMode ? null : () => addToCart(btn);

    // Set position and size
    btn.style.position = "absolute";
    const pos = normalizePos(buttonPositions[article.id]);
    buttonPositions[article.id] = pos;
    applyTilePos(btn, pos);
    btn.style.setProperty("--tile-invert", invertColor(article.color || "#007bff"));
    if (isEditMode && article.id === selectedTileId) {
      btn.classList.add("selected");
    }

    btn.innerHTML += `
			<span class="name">${escapeHtml(article.name)}</span>
			<span class="price">${Number(article.price).toFixed(2)} €</span>
		`;

    grid.appendChild(btn);
  });
}

// Update available products list
function updateAvailableProducts(visibleIds) {
  // For compatibility, but not used anymore
}

// Update available products in edit panel
function updateEditAvailableProducts() {
  const editGrid = document.getElementById("editAvailableProducts");
  if (!editGrid) return;

  const savedOrder = localStorage.getItem("register_visible_products");
  const visibleIds = savedOrder
    ? JSON.parse(savedOrder)
    : articles.map((a) => a.id);
  const availableArticles = articles.filter(
    (article) => !visibleIds.includes(article.id),
  );

  editGrid.innerHTML = "";

  if (availableArticles.length === 0) {
    editGrid.innerHTML =
      '<p style="text-align: center; opacity: 0.6; padding: 20px;">' +
      escapeHtml(translations.all_products_visible) +
      "</p>";
    return;
  }

  availableArticles.forEach((article) => {
    const btn = document.createElement("button");
    btn.className = "add-product-btn";
    btn.textContent = article.name;
    btn.setAttribute("data-id", article.id);
    btn.setAttribute("draggable", "true");
    btn.ondragstart = (e) => {
      e.dataTransfer.setData("text/plain", article.id);
      e.dataTransfer.effectAllowed = "copy";
      e.dataTransfer.dropEffect = "copy";
    };
    editGrid.appendChild(btn);
  });
}

// Toggle edit mode
function toggleEditMode() {
  isEditMode = !isEditMode;
  const btn = document.getElementById("editModeBtn");
  const body = document.body;
  const billPanel = document.getElementById("billPanel");
  const editPanel = document.getElementById("editPanel");
  const grid = document.getElementById("productsGrid");

  if (isEditMode) {
    btn.textContent = "✓ " + translations.done_editing;
    btn.classList.add("active");
    body.classList.add("edit-mode");
    billPanel.classList.add("edit-mode");
    editPanel.classList.add("show");
    grid.classList.add("edit-mode");
    updateEditAvailableProducts();
  } else {
    btn.textContent = "✏️ " + translations.edit_layout;
    selectedTileId = null;
    btn.classList.remove("active");
    body.classList.remove("edit-mode");
    billPanel.classList.remove("edit-mode");
    editPanel.classList.remove("show");
    grid.classList.remove("edit-mode");
  }

  renderProductsGrid();
  setupInteractions();
}

// Grid geometry: tiles always cover whole cells and keep a constant gap
const GRID_CELL = 55; // px per cell (50px tile + 5px gap)
const GRID_GAP = 5; // px between two tiles
const GRID_COLS = 20;
const GRID_ROWS = 10;
const TILE_INSET = Math.ceil(GRID_GAP / 2); // offset of a tile inside its cell

// Currently selected tile in edit mode
let selectedTileId = null;

// Round any stored position to whole cells (also repairs older layouts)
function normalizePos(pos) {
  const cols = Math.max(1, Math.round((Number(pos && pos.width) || GRID_CELL) / GRID_CELL));
  const rows = Math.max(1, Math.round((Number(pos && pos.height) || GRID_CELL) / GRID_CELL));
  const w = Math.min(cols, GRID_COLS);
  const h = Math.min(rows, GRID_ROWS);
  const cx = Math.min(Math.max(0, Math.round((Number(pos && pos.x) || 0) / GRID_CELL)), GRID_COLS - w);
  const cy = Math.min(Math.max(0, Math.round((Number(pos && pos.y) || 0) / GRID_CELL)), GRID_ROWS - h);
  return {
    x: cx * GRID_CELL,
    y: cy * GRID_CELL,
    width: w * GRID_CELL,
    height: h * GRID_CELL,
  };
}

// Apply a (cell aligned) position to a tile element
function applyTilePos(btn, pos) {
  btn.style.left = pos.x + TILE_INSET + "px";
  btn.style.top = pos.y + TILE_INSET + "px";
  btn.style.width = pos.width - GRID_GAP + "px";
  btn.style.height = pos.height - GRID_GAP + "px";
}

function getVisibleIds() {
  const savedOrder = localStorage.getItem("register_visible_products");
  return savedOrder ? JSON.parse(savedOrder) : [];
}

function saveButtonPositions() {
  localStorage.setItem("register_button_positions", JSON.stringify(buttonPositions));
}

// Does the given cell rect fit into the grid without touching other tiles?
function isFreeSpot(id, rect) {
  if (
    rect.x < 0 ||
    rect.y < 0 ||
    rect.x + rect.width > GRID_COLS * GRID_CELL ||
    rect.y + rect.height > GRID_ROWS * GRID_CELL
  ) {
    return false;
  }
  return !getVisibleIds().some((otherId) => {
    if (otherId === id || !buttonPositions[otherId]) return false;
    const o = normalizePos(buttonPositions[otherId]);
    return !(
      rect.x + rect.width <= o.x ||
      rect.x >= o.x + o.width ||
      rect.y + rect.height <= o.y ||
      rect.y >= o.y + o.height
    );
  });
}

// Inverted color (used for the selection frame)
function invertColor(hex) {
  const m = /^#?([0-9a-f]{6})$/i.exec(hex || "");
  if (!m) return "#ffffff";
  const inv = (0xffffff ^ parseInt(m[1], 16)).toString(16).padStart(6, "0");
  return "#" + inv;
}

function selectTile(id) {
  selectedTileId = id;
  document.querySelectorAll("#productsGrid .product-btn").forEach((btn) => {
    btn.classList.toggle("selected", btn.dataset.id === id);
  });
}

// Preview of the target cells while dragging / resizing
function showDropPreview(rect, ok) {
  const grid = document.getElementById("productsGrid");
  if (!grid) return;
  let preview = grid.querySelector(".drop-preview");
  if (!preview) {
    preview = document.createElement("div");
    preview.className = "drop-preview";
    grid.appendChild(preview);
  }
  applyTilePos(preview, rect);
  preview.classList.toggle("invalid", !ok);
}

function hideDropPreview() {
  const preview = document.querySelector("#productsGrid .drop-preview");
  if (preview) preview.remove();
}

// Snapped cell rect for a tile whose top-left corner is at viewport (left, top)
function snappedRectAt(id, left, top) {
  const grid = document.getElementById("productsGrid");
  const gridRect = grid.getBoundingClientRect();
  const pos = normalizePos(buttonPositions[id]);
  return {
    x: Math.round((left - gridRect.left - TILE_INSET) / GRID_CELL) * GRID_CELL,
    y: Math.round((top - gridRect.top - TILE_INSET) / GRID_CELL) * GRID_CELL,
    width: pos.width,
    height: pos.height,
  };
}

// Setup interactions for edit mode
function setupInteractions() {
  // Unset any existing interactions
  interact(".product-btn").unset();
  interact(".edit-panel").unset();
  interact(".add-product-btn").unset();
  interact(".products-grid").unset();

  // Auch die .add-product-btn Buttons als Interact.js-Draggables initialisieren (jetzt nach unset!)
  if (isEditMode) {
    interact(".add-product-btn").draggable({
      listeners: {
        start(event) {
          const target = event.target;
          target.classList.add("dragging");
          target._origZ = target.style.zIndex;
          target._origPos = target.style.position;
          target._origLeft = target.style.left;
          target._origTop = target.style.top;
          target._origPointer = target.style.pointerEvents;
          target.style.zIndex = 10000;
          target.style.position = 'fixed';
          target.style.pointerEvents = 'none';
          const rect = target.getBoundingClientRect();
          target._dragOffsetX = event.client.x - rect.left;
          target._dragOffsetY = event.client.y - rect.top;
        },
        move(event) {
          const target = event.target;
          target.style.left = event.client.x - target._dragOffsetX + 'px';
          target.style.top = event.client.y - target._dragOffsetY + 'px';
        },
        end(event) {
          const target = event.target;
          target.classList.remove("dragging");
          target.style.zIndex = target._origZ || '';
          target.style.position = target._origPos || '';
          target.style.left = target._origLeft || '';
          target.style.top = target._origTop || '';
          target.style.pointerEvents = target._origPointer || '';
        },
      },
    });
  }

  if (isEditMode) {
    let droppedInZone = false;

    // Native dragover-Handler, damit Drop akzeptiert wird
    const grid = document.getElementById("productsGrid");
    if (grid && !grid._dragoverBound) {
      grid._dragoverBound = true;
      grid.addEventListener(
        "dragover",
        function (event) {
          event.preventDefault();
        },
        { passive: false },
      );
    }

    interact(".product-btn")
      .on("down", function (event) {
        const btn = event.currentTarget;
        if (btn && btn.dataset && btn.dataset.id) selectTile(btn.dataset.id);
      })
      .draggable({
        listeners: {
          start(event) {
            const target = event.target;
            selectTile(target.dataset.id);
            target.classList.add("dragging");
            droppedInZone = false;
            const rect = target.getBoundingClientRect();
            target._dragOffsetX = event.client.x - rect.left;
            target._dragOffsetY = event.client.y - rect.top;
            // Über alles legen, damit es auch auf die Ablagefläche gezogen werden kann
            target.style.zIndex = 10000;
            target.style.position = "fixed";
            target.style.pointerEvents = "none";
            target.style.left = rect.left + "px";
            target.style.top = rect.top + "px";
          },
          move(event) {
            const target = event.target;
            const left = event.client.x - target._dragOffsetX;
            const top = event.client.y - target._dragOffsetY;
            target.style.left = left + "px";
            target.style.top = top + "px";
            const rect = snappedRectAt(target.dataset.id, left, top);
            showDropPreview(rect, isFreeSpot(target.dataset.id, rect));
          },
          end(event) {
            const target = event.target;
            const id = target.dataset.id;
            hideDropPreview();
            target.classList.remove("dragging");
            if (droppedInZone) {
              removeProduct(id);
              return;
            }
            const rect = snappedRectAt(
              id,
              event.client.x - target._dragOffsetX,
              event.client.y - target._dragOffsetY,
            );
            if (isFreeSpot(id, rect)) {
              buttonPositions[id] = rect;
              saveButtonPositions();
            }
            renderProductsGrid();
            setupInteractions();
          },
        },
      })
      .resizable({
        edges: {
          right: true,
          bottom: true,
        },
        listeners: {
          start(event) {
            const target = event.target;
            selectTile(target.dataset.id);
            target.classList.add("resizing");
            target._startPos = normalizePos(buttonPositions[target.dataset.id]);
          },
          move(event) {
            const target = event.target;
            const start = target._startPos;
            // Only whole cells: round the dragged size to the nearest cell count
            const cols = Math.max(1, Math.round((event.rect.width + GRID_GAP) / GRID_CELL));
            const rows = Math.max(1, Math.round((event.rect.height + GRID_GAP) / GRID_CELL));
            const rect = {
              x: start.x,
              y: start.y,
              width: Math.min(cols, GRID_COLS - start.x / GRID_CELL) * GRID_CELL,
              height: Math.min(rows, GRID_ROWS - start.y / GRID_CELL) * GRID_CELL,
            };
            target._newRect = rect;
            applyTilePos(target, rect);
            const ok = isFreeSpot(target.dataset.id, rect);
            target.classList.toggle("invalid", !ok);
          },
          end(event) {
            const target = event.target;
            const id = target.dataset.id;
            target.classList.remove("resizing", "invalid");
            if (target._newRect && isFreeSpot(id, target._newRect)) {
              buttonPositions[id] = target._newRect;
              saveButtonPositions();
            }
            target._newRect = null;
            renderProductsGrid();
            setupInteractions();
          },
        },
      });

    // Drop zone für neue Produkte
    interact(".products-grid").dropzone({
      ondrop: function (event) {
        const id = event.relatedTarget.getAttribute("data-id");
        const visibleIds = getVisibleIds();
        if (visibleIds.includes(id)) return;
        const grid = document.getElementById("productsGrid");
        const gridRect = grid.getBoundingClientRect();
        const rect = {
          x: Math.floor((event.dragEvent.client.x - gridRect.left) / GRID_CELL) * GRID_CELL,
          y: Math.floor((event.dragEvent.client.y - gridRect.top) / GRID_CELL) * GRID_CELL,
          width: GRID_CELL,
          height: GRID_CELL,
        };
        if (!isFreeSpot(id, rect)) return;
        visibleIds.push(id);
        localStorage.setItem("register_visible_products", JSON.stringify(visibleIds));
        buttonPositions[id] = rect;
        saveButtonPositions();
        selectedTileId = id;
        renderProductsGrid(visibleIds);
        updateEditAvailableProducts();
        setupInteractions();
      },
    });

    // Drop zone for removing products
    interact(".edit-panel").dropzone({
      ondrop: function (event) {
        droppedInZone = true;
      },
    });
  }
}

// Remove product from grid
function removeProduct(id) {
  const savedOrder = localStorage.getItem("register_visible_products");
  let visibleIds = savedOrder
    ? JSON.parse(savedOrder)
    : articles.map((a) => a.id);
  visibleIds = visibleIds.filter((vid) => vid !== id);
  localStorage.setItem("register_visible_products", JSON.stringify(visibleIds));
  renderProductsGrid(visibleIds);
  updateEditAvailableProducts();
  setupInteractions();
}

// Add product to grid
function addProduct(id) {
  const savedOrder = localStorage.getItem("register_visible_products");
  let visibleIds = savedOrder
    ? JSON.parse(savedOrder)
    : articles.map((a) => a.id);
  if (!visibleIds.includes(id)) {
    visibleIds.push(id);
    localStorage.setItem(
      "register_visible_products",
      JSON.stringify(visibleIds),
    );

    // Set default position if not exists
    if (!buttonPositions[id]) {
      buttonPositions[id] = {
        x: 0,
        y: 0,
        width: GRID_CELL,
        height: GRID_CELL,
      };
      localStorage.setItem(
        "register_button_positions",
        JSON.stringify(buttonPositions),
      );
    }

    renderProductsGrid(visibleIds);
    setupInteractions();
  }
}

// Add item to cart
function addToCart(btn) {
  const id = btn.dataset.id;
  const name = btn.dataset.name;
  const price = parseFloat(btn.dataset.price);

  if (cart[id]) {
    cart[id].qty++;
  } else {
    cart[id] = {
      id,
      name,
      price,
      qty: 1,
    };
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
  const billItems = document.getElementById("billItems");
  const totalEl = document.getElementById("totalAmount");
  const payCashBtn = document.getElementById("payCashBtn");
  const payCardBtn = document.getElementById("payCardBtn");

  const items = Object.values(cart);

  if (items.length === 0) {
    billItems.innerHTML = `
			<div class="empty-bill">
				<p>${escapeHtml(translations.no_items_yet)}</p>
				<p>${escapeHtml(translations.click_product_to_add)}</p>
			</div>
		`;
    totalEl.textContent = "0.00 €";
    payCashBtn.disabled = true;
    payCardBtn.disabled = true;
    return;
  }

  let html = "";
  let total = 0;

  items.forEach((item) => {
    const itemTotal = item.price * item.qty;
    total += itemTotal;
    html += `
			<div class="bill-item">
				<div class="item-info">
					<div class="item-name">${escapeHtml(item.name)}</div>
					<div class="item-price">${escapeHtml(translations.price_each.replace('%s', item.price.toFixed(2)))}</div>
				</div>
				<span class="item-qty">×${item.qty}</span>
				<button class="remove-btn" onclick="removeFromCart('${item.id}')">−</button>
			</div>
		`;
  });

  billItems.innerHTML = html;
  totalEl.textContent = total.toFixed(2) + " €";
  payCashBtn.disabled = false;
  payCardBtn.disabled = false;
}

// Process payment
function pay(method) {
  const items = Object.values(cart);
  if (items.length === 0) return;

  const total = items.reduce((sum, item) => sum + item.price * item.qty, 0);

  // Send to server
  const layoutSelect = document.getElementById('layoutSelect');
  const layout = layoutSelect && layoutSelect.value ? layoutSelect.value : '';
  fetch("api/payment.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      items: items,
      total: total,
      method: method,
      layout: layout,
      timestamp: Math.floor(Date.now() / 1000), // Client timestamp
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showSuccess(method, total);
        clearCart();
      } else {
        alert(translations.payment_failed + ": " + (data.error || translations.unknown_error));
      }
    })
    .catch((err) => {
      console.error("Payment error:", err);
      alert(translations.payment_failed_try_again);
    });
}

// Show success overlay
function showSuccess(method, total) {
  const overlay = document.getElementById("successOverlay");
  const details = document.getElementById("successDetails");
  details.textContent = translations.paid_by
    .replace("%s", total.toFixed(2))
    .replace("%s", translations[method] || method);
  overlay.classList.add("show");

  setTimeout(() => {
    overlay.classList.remove("show");
  }, 2000);
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

// Initialize
window.addEventListener("DOMContentLoaded", function () {
  loadLayout();
  setupInteractions();
});

// --- Layout-Management (Name nur aus Inputfeld) ---
window.addEventListener('DOMContentLoaded', function() {
  const layoutSelect = document.getElementById('layoutSelect');
  const saveLayoutBtn = document.getElementById('saveLayoutBtn');
  const LAYOUT_KEY = 'register_last_layout';



  async function fetchLayouts() {
    const res = await fetch('api/layouts.php?action=list');
    const data = await res.json();
    if (data.success) {
      layoutSelect.innerHTML = '';
      // Leeres Element "Neu" immer oben
      const emptyOpt = document.createElement('option');
      emptyOpt.value = '';
      emptyOpt.textContent = translations.layout_new;
      layoutSelect.appendChild(emptyOpt);
      data.layouts.forEach(name => {
        const opt = document.createElement('option');
        opt.value = name;
        opt.textContent = name;
        layoutSelect.appendChild(opt);
      });
      renderLayoutButtons(data.layouts);
      // Zuletzt geladenes Layout selektieren
      const last = localStorage.getItem(LAYOUT_KEY);
      if (last && data.layouts.includes(last)) {
        layoutSelect.value = last;
        await loadLayoutFromServer(last);
      } else {
        layoutSelect.value = '';
      }
      updateLayoutButtons();
    }
  }

  // One button per saved layout for quick switching
  function renderLayoutButtons(names) {
    const bar = document.getElementById('layoutButtons');
    if (!bar) return;
    bar.innerHTML = '';
    names.forEach(name => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'layout-switch-btn';
      btn.textContent = name;
      btn.dataset.layout = name;
      btn.addEventListener('click', () => {
        layoutSelect.value = name;
        loadLayoutFromServer(name);
      });
      bar.appendChild(btn);
    });
    updateLayoutButtons();
  }

  function updateLayoutButtons() {
    const current = layoutSelect ? layoutSelect.value : '';
    document.querySelectorAll('#layoutButtons .layout-switch-btn').forEach(btn => {
      const active = btn.dataset.layout === current;
      btn.classList.toggle('active', active);
      btn.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
  }

  async function loadLayoutFromServer(name) {
    if (!name) return;
    const res = await fetch('api/layouts.php?action=load&name=' + encodeURIComponent(name));
    const data = await res.json();
    if (data.success) {
      // Layout anwenden
      if (data.data) {
        if (data.data.buttonPositions) buttonPositions = data.data.buttonPositions;
        if (data.data.visibleIds) localStorage.setItem('register_visible_products', JSON.stringify(data.data.visibleIds));
        localStorage.setItem('register_button_positions', JSON.stringify(buttonPositions));
        localStorage.setItem(LAYOUT_KEY, name);
        renderProductsGrid();
        if (isEditMode) updateEditAvailableProducts();
        setupInteractions();
        updateLayoutButtons();
      }
    } else {
      alert(translations.layout_load_failed + ': ' + (data.error || translations.unknown_error));
    }
  }

  async function saveLayoutToServer() {
    let name = layoutSelect.value;
    if (!name) {
      name = window.prompt(translations.layout_name_prompt);
      if (!name) {
        alert(translations.layout_no_name_entered);
        return;
      }
    }
    const visibleIds = JSON.parse(localStorage.getItem('register_visible_products') || '[]');
    const payload = {
      name,
      data: {
        buttonPositions,
        visibleIds
      }
    };
    const res = await fetch('api/layouts.php?action=save', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
      await fetchLayouts();
      layoutSelect.value = name;
      localStorage.setItem(LAYOUT_KEY, name);
      alert(translations.layout_saved);
    } else {
      alert(translations.layout_save_failed + ': ' + (data.error || translations.unknown_error));
    }
  }

  if (layoutSelect) {
    layoutSelect.addEventListener('change', e => {
      if (layoutSelect.value) {
        loadLayoutFromServer(layoutSelect.value);
      } else {
        updateLayoutButtons();
      }
    });
    fetchLayouts();
  }

  if (saveLayoutBtn) {
    saveLayoutBtn.addEventListener('click', saveLayoutToServer);
  }
});

// --- Adjustable width of the right column (stored per device) ---
window.addEventListener('DOMContentLoaded', function () {
  const resizer = document.getElementById('panelResizer');
  const container = document.querySelector('.main-container');
  if (!resizer || !container) return;

  const KEY = 'register_side_panel_width';
  const MIN = 220;
  const DEFAULT = 300;
  const maxWidth = () => Math.max(MIN, Math.round(window.innerWidth * 0.6));

  function apply(width) {
    const w = Math.min(Math.max(MIN, Math.round(width)), maxWidth());
    document.documentElement.style.setProperty('--side-panel-width', w + 'px');
    return w;
  }

  let stored = null;
  try { stored = parseInt(localStorage.getItem(KEY), 10); } catch (e) {}
  if (stored) apply(stored);

  let dragging = false;
  let current = stored || DEFAULT;

  resizer.addEventListener('pointerdown', (e) => {
    dragging = true;
    resizer.setPointerCapture(e.pointerId);
    resizer.classList.add('active');
    document.body.classList.add('resizing-panel');
    e.preventDefault();
  });
  resizer.addEventListener('pointermove', (e) => {
    if (!dragging) return;
    const rect = container.getBoundingClientRect();
    current = apply(rect.right - e.clientX);
  });
  const stop = (e) => {
    if (!dragging) return;
    dragging = false;
    resizer.classList.remove('active');
    document.body.classList.remove('resizing-panel');
    try { localStorage.setItem(KEY, String(current)); } catch (err) {}
  };
  resizer.addEventListener('pointerup', stop);
  resizer.addEventListener('pointercancel', stop);
  // Double click resets to default width
  resizer.addEventListener('dblclick', () => {
    current = apply(DEFAULT);
    try { localStorage.removeItem(KEY); } catch (err) {}
  });
  window.addEventListener('resize', () => apply(current));
});

// Keep session alive by pinging server every 5 minutes
setInterval(() => {
  fetch('api/keepalive.php')
    .then(response => response.json())
    .then(data => {
      if (data.status !== 'ok') {
        console.warn('Keep-alive failed');
      }
    })
    .catch(error => console.error('Keep-alive error:', error));
}, 5 * 60 * 1000); // 5 minutes
