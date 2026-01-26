<?php
/**
 * Article class
 */
class Article {
    public $id;
    public $name;
    public $price;
    public $color;
    public $created_at;
    public $textColor;

    public function __construct($data) {
        $this->id = $data['id'] ?? '';
        $this->name = $data['name'] ?? '';
        $this->price = $data['price'] ?? 0;
        $this->color = $data['color'] ?? '#007bff';
        $this->created_at = $data['created_at'] ?? '';
        // textColor wird ggf. später gesetzt
        $this->textColor = $data['textColor'] ?? null;
    }

    public function toArray() {
        $arr = [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'color' => $this->color,
            'created_at' => $this->created_at
        ];
        if ($this->textColor) $arr['textColor'] = $this->textColor;
        return $arr;
    }
}

/**
 * Transaction class
 */
class Transaction {
    public $id;
    public $items;
    public $total;
    public $payment_method;
    public $timestamp;

    public function __construct($data) {
        $this->id = $data['id'] ?? '';
        $this->items = $data['items'] ?? [];
        $this->total = $data['total'] ?? 0;
        $this->payment_method = $data['payment_method'] ?? '';
        $this->timestamp = $data['timestamp'] ?? '';
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'items' => $this->items,
            'total' => $this->total,
            'payment_method' => $this->payment_method,
            'timestamp' => $this->timestamp
        ];
    }
}

/**
 * Layout class
 */
class Layout {
    public $button_positions;
    public $visible_products;

    public function __construct($positions = [], $visible = []) {
        $this->button_positions = $positions;
        $this->visible_products = $visible;
    }

    public function toArray() {
        return [
            'button_positions' => $this->button_positions,
            'visible_products' => $this->visible_products
        ];
    }
}
?>