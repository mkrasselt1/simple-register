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

  // Remove products positioned outside the grid
  const gridWidth = 1095; // 20 * 50 + 19 * 5
  const gridHeight = 545; // 10 * 50 + 9 * 5
  visibleIds = visibleIds.filter((id) => {
    const pos = buttonPositions[id];
    if (!pos) return true; // keep if no position
    return (
      pos.x >= 0 &&
      pos.y >= 0 &&
      pos.x + pos.width <= gridWidth &&
      pos.y + pos.height <= gridHeight
    );
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
    const pos = buttonPositions[article.id] || {
      x: 0,
      y: 0,
      width: 55,
      height: 55,
    };
    // Sichtbare Fläche 2px kleiner pro Seite
    btn.style.left = (pos.x + 2) + "px";
    btn.style.top = (pos.y + 2) + "px";
    btn.style.width = (pos.width - 4) + "px";
    btn.style.height = (pos.height - 4) + "px";

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
      '<p style="text-align: center; opacity: 0.6; padding: 20px;">All products are already visible</p>';
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
    btn.textContent = "✓ Done Editing";
    btn.classList.add("active");
    body.classList.add("edit-mode");
    billPanel.classList.add("edit-mode");
    editPanel.classList.add("show");
    grid.classList.add("edit-mode");
    updateEditAvailableProducts();
  } else {
    btn.textContent = "✏️ Edit Layout";
    btn.classList.remove("active");
    body.classList.remove("edit-mode");
    billPanel.classList.remove("edit-mode");
    editPanel.classList.remove("show");
    grid.classList.remove("edit-mode");
  }

  renderProductsGrid();
  setupInteractions();
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
          // Offset wie bei .product-btn berechnen
          const rect = target.getBoundingClientRect();
          target._dragOffsetX = event.client.x - rect.left;
          target._dragOffsetY = event.client.y - rect.top;
        },
        move(event) {
          const target = event.target;
          // Button direkt unter die Maus setzen
          const x = event.client.x - target._dragOffsetX;
          const y = event.client.y - target._dragOffsetY;
          target.style.left = x + 'px';
          target.style.top = y + 'px';
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
    if (grid) {
      grid.addEventListener(
        "dragover",
        function (event) {
          event.preventDefault();
        },
        { passive: false },
      );
    }

    interact(".product-btn")
      .draggable({
        listeners: {
              start(event) {
                if (event.target.closest(".remove-btn")) return;
                event.target.classList.add("dragging");
                droppedInZone = false;
                // Button über alles legen
                const target = event.target;
                target._origZ = target.style.zIndex;
                target._origPos = target.style.position;
                target._origPointer = target.style.pointerEvents;
                target.style.zIndex = 10000;
                target.style.position = "fixed";
                target.style.pointerEvents = "none";
                // Offset jetzt berechnen (nach fixed!)
                const grid = document.getElementById("productsGrid");
                const gridRect = grid.getBoundingClientRect();
                const rect = target.getBoundingClientRect();
                target._dragOffsetX = event.client.x - rect.left - gridRect.left;
                target._dragOffsetY = event.client.y - rect.top - gridRect.top;
                let dot = document.createElement('div');
                dot.className = 'drag-offset-dot';
                dot.style.position = 'absolute';
                dot.style.left = target._dragOffsetX - 5 + 'px';
                dot.style.top = target._dragOffsetY - 5 + 'px';
                dot.style.width = '10px';
                dot.style.height = '10px';
                dot.style.background = 'red';
                dot.style.borderRadius = '50%';
                dot.style.pointerEvents = 'none';
                dot.style.zIndex = 20000;
                dot.setAttribute('data-dot', '1');
                target.appendChild(dot);
          },
          move(event) {
            const grid = document.getElementById("productsGrid");
            const gridRect = grid.getBoundingClientRect();
            const target = event.target;
            let x = event.client.x + event.dx - target._dragOffsetX;
            let y = event.client.y + event.dy - target._dragOffsetY;
            target.style.left = x + "px";
            target.style.top = y + "px";
          },
          end(event) {
              const target = event.target;
              // Punkt entfernen
              const dot = target.querySelector('.drag-offset-dot');
              if (dot) dot.remove();
              target.classList.remove("dragging");
              if (droppedInZone) {
                target.style.left = "";
                target.style.top = "";
                const id = event.target.dataset.id;
                removeProduct(id);
              } else {
                // Neue Position relativ zum Grid berechnen und speichern (Offset beachten)
                const grid = document.getElementById("productsGrid");
                const gridRect = grid.getBoundingClientRect();
                const mouseX = event.client.x;
                const mouseY = event.client.y;
                // Offset aus Drag-Start verwenden
                const offsetX = target._dragOffsetX || 0;
                const offsetY = target._dragOffsetY || 0;
                let x = mouseX - gridRect.left - offsetX;
                let y = mouseY - gridRect.top - offsetY;
                // Snap auf 55er Grid
                x = Math.round(x / 55) * 55;
                y = Math.round(y / 55) * 55;
                // Kollisionsprüfung
                const newRect = {
                  left: x,
                  top: y,
                  right: x + target.offsetWidth,
                  bottom: y + target.offsetHeight,
                };
                const buttons = Array.from(grid.querySelectorAll(".product-btn")).filter((btn) => btn !== target);
                const hasOverlap = buttons.some((btn) => {
                  const btnRect = {
                    left: parseFloat(btn.style.left),
                    top: parseFloat(btn.style.top),
                    right: parseFloat(btn.style.left) + btn.offsetWidth,
                    bottom: parseFloat(btn.style.top) + btn.offsetHeight,
                  };
                  return !(
                    newRect.right <= btnRect.left ||
                    newRect.left >= btnRect.right ||
                    newRect.bottom <= btnRect.top ||
                    newRect.top >= btnRect.bottom
                  );
                });
                const id = target.dataset.id;
                if (hasOverlap) {
                  // Reset auf alte Position und UI neu rendern
                  const pos = buttonPositions[id];
                  target.style.left = pos.x + "px";
                  target.style.top = pos.y + "px";
                  target.style.width = pos.width + "px";
                  target.style.height = pos.height + "px";
                  target.style.transform = "";
                  target.setAttribute("data-x", 0);
                  target.setAttribute("data-y", 0);
                  renderProductsGrid();
                  setupInteractions();
                  return;
                }
                buttonPositions[id] = buttonPositions[id] || {};
                buttonPositions[id].x = x;
                buttonPositions[id].y = y;
                buttonPositions[id].width = target.offsetWidth;
                buttonPositions[id].height = target.offsetHeight;
                localStorage.setItem(
                  "register_button_positions",
                  JSON.stringify(buttonPositions),
                );
                renderProductsGrid();
                setupInteractions();
              }
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
            if (event.target.closest(".remove-btn")) return;
            // Save original size for collision check
            event.target._originalWidth = event.target.offsetWidth;
            event.target._originalHeight = event.target.offsetHeight;
          },
          move(event) {
            const target = event.target;
            let x = parseFloat(target.getAttribute("data-x")) || 0;
            let y = parseFloat(target.getAttribute("data-y")) || 0;
            let width = event.rect.width;
            let height = event.rect.height;
            // Snap size to 55px grid
            width = Math.round(width / 55) * 55;
            height = Math.round(height / 55) * 55;
            target.style.width = width + "px";
            target.style.height = height + "px";
            x += event.deltaRect.left;
            y += event.deltaRect.top;
            target.style.transform = "translate(" + x + "px," + y + "px)";
            target.setAttribute("data-x", x);
            target.setAttribute("data-y", y);
          },
          end(event) {
            const target = event.target;
            const id = target.dataset.id;
            const newWidth = target.offsetWidth;
            const newHeight = target.offsetHeight;

            // Check for collision
            const grid = document.getElementById("productsGrid");
            const newRect = {
              left: target.offsetLeft,
              top: target.offsetTop,
              right: target.offsetLeft + newWidth,
              bottom: target.offsetTop + newHeight,
            };

            const buttons = Array.from(
              grid.querySelectorAll(".product-btn"),
            ).filter((btn) => btn !== target);
            const hasOverlap = buttons.some((btn) => {
              const btnRect = {
                left: btn.offsetLeft,
                top: btn.offsetTop,
                right: btn.offsetLeft + btn.offsetWidth,
                bottom: btn.offsetTop + btn.offsetHeight,
              };
              return !(
                newRect.right <= btnRect.left ||
                newRect.left >= btnRect.right ||
                newRect.bottom <= btnRect.top ||
                newRect.top >= btnRect.bottom
              );
            });

            if (hasOverlap) {
              // Revert to original size
              target.style.width = target._originalWidth + "px";
              target.style.height = target._originalHeight + "px";
              buttonPositions[id].width = target._originalWidth;
              buttonPositions[id].height = target._originalHeight;
            } else {
              buttonPositions[id] = buttonPositions[id] || {
                x: 0,
                y: 0,
              };
              buttonPositions[id].width = newWidth;
              buttonPositions[id].height = newHeight;
            }

            localStorage.setItem(
              "register_button_positions",
              JSON.stringify(buttonPositions),
            );
            target.style.transform = "";
            target.setAttribute("data-x", 0);
            target.setAttribute("data-y", 0);
          },
        },
      });

    // Drop zone für neue Produkte
    interact(".products-grid").dropzone({
      ondrop: function (event) {
        const id = event.relatedTarget.getAttribute("data-id");
        // Nur hinzufügen, wenn noch nicht sichtbar
        const savedOrder = localStorage.getItem("register_visible_products");
        let visibleIds = savedOrder ? JSON.parse(savedOrder) : [];
        if (!visibleIds.includes(id)) {
          // Position aus Drop berechnen
          const grid = document.getElementById("productsGrid");
          const gridRect = grid.getBoundingClientRect();
          const dropX = event.dragEvent.client.x - gridRect.left;
          const dropY = event.dragEvent.client.y - gridRect.top;
          // Snap auf 55er Grid
          const x = Math.round(dropX / 55) * 55;
          const y = Math.round(dropY / 55) * 55;
          visibleIds.push(id);
          localStorage.setItem(
            "register_visible_products",
            JSON.stringify(visibleIds),
          );
          buttonPositions[id] = {
            x: x,
            y: y,
            width: 55,
            height: 55,
          };
          localStorage.setItem(
            "register_button_positions",
            JSON.stringify(buttonPositions),
          );
          // Debug-Ausgabe
          console.log("Nach Drop, visibleIds:", visibleIds);
          // UI komplett neu aufbauen
          renderProductsGrid(visibleIds);
          updateEditAvailableProducts();
          setupInteractions();
        }
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

// Snap and save position after drag
function snapAndSavePosition(target) {
  const grid = document.getElementById("productsGrid");
  const gridRect = grid.getBoundingClientRect();
  const rect = target.getBoundingClientRect();
  let x = rect.left - gridRect.left;
  let y = rect.top - gridRect.top;

  // Snap to 55px grid
  x = Math.round(x / 55) * 55;
  y = Math.round(y / 55) * 55;

  // Check for overlap
  const newRect = {
    left: x,
    top: y,
    right: x + target.offsetWidth,
    bottom: y + target.offsetHeight,
  };

  const buttons = Array.from(grid.querySelectorAll(".product-btn")).filter(
    (btn) => btn !== target,
  );
  const hasOverlap = buttons.some((btn) => {
    const btnRect = btn.getBoundingClientRect();
    const gridBtnRect = {
      left: btnRect.left - gridRect.left,
      top: btnRect.top - gridRect.top,
      right: btnRect.right - gridRect.left,
      bottom: btnRect.bottom - gridRect.top,
    };
    return !(
      newRect.right <= gridBtnRect.left ||
      newRect.left >= gridBtnRect.right ||
      newRect.bottom <= gridBtnRect.top ||
      newRect.top >= gridBtnRect.bottom
    );
  });

  if (hasOverlap) {
    // Reset to original position
    const id = target.dataset.id;
    const pos = buttonPositions[id];
    target.style.left = pos.x + "px";
    target.style.top = pos.y + "px";
    target.style.width = pos.width + "px";
    target.style.height = pos.height + "px";
    target.style.transform = "";
    target.setAttribute("data-x", 0);
    target.setAttribute("data-y", 0);
    return;
  }

  // Save new position
  const id = target.dataset.id;
  buttonPositions[id] = buttonPositions[id] || {};
  buttonPositions[id].x = x;
  buttonPositions[id].y = y;
  buttonPositions[id].width = target.offsetWidth;
  buttonPositions[id].height = target.offsetHeight;
  localStorage.setItem(
    "register_button_positions",
    JSON.stringify(buttonPositions),
  );
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
        width: 55,
        height: 55,
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
				<p>No items yet</p>
				<p>Click a product to add it</p>
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
					<div class="item-price">${item.price.toFixed(2)} € each</div>
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
  details.textContent = `${total.toFixed(2)} € paid by ${method}`;
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
      emptyOpt.textContent = 'Neu';
      layoutSelect.appendChild(emptyOpt);
      data.layouts.forEach(name => {
        const opt = document.createElement('option');
        opt.value = name;
        opt.textContent = name;
        layoutSelect.appendChild(opt);
      });
      // Zuletzt geladenes Layout selektieren
      const last = localStorage.getItem(LAYOUT_KEY);
      if (last && data.layouts.includes(last)) {
        layoutSelect.value = last;
        await loadLayoutFromServer(last);
      } else {
        layoutSelect.value = '';
      }
      setLayoutNameInputVisibility();
    }
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
        setupInteractions();
      }
    } else {
      alert('Layout konnte nicht geladen werden: ' + (data.error || 'Unbekannter Fehler'));
    }
  }

  async function saveLayoutToServer() {
    let name = layoutSelect.value;
    if (!name) {
      name = window.prompt('Bitte einen Namen für das neue Layout eingeben:');
      if (!name) {
        alert('Kein Name eingegeben. Layout wurde nicht gespeichert.');
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
      alert('Layout gespeichert!');
    } else {
      alert('Fehler beim Speichern: ' + (data.error || 'Unbekannter Fehler'));
    }
  }

  if (layoutSelect) {
    layoutSelect.addEventListener('change', e => {
      if (layoutSelect.value) {
        loadLayoutFromServer(layoutSelect.value);
      }
    });
    fetchLayouts();
  }

  if (saveLayoutBtn) {
    saveLayoutBtn.addEventListener('click', saveLayoutToServer);
  }
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
