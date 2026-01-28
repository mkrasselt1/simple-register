<?php
class TransactionItem {
    private $id;
    private $name;
    private $qty;
    private $price;

    public function __construct($id, $name, $qty, $price) {
        $this->id = $id;
        $this->name = $name;
        $this->qty = $qty;
        $this->price = $price;
    }
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getQty() { return $this->qty; }
    public function getPrice() { return $this->price; }
    public function toArray() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'qty' => $this->qty,
            'price' => $this->price
        ];
    }
}

class Transaction {
    private $id;
    private $items = [];
    private $total;
    private $paymentMethod;
    private $timestamp;
    private $layout;
    private $cancelled;

    public function __construct($id, $items, $total, $paymentMethod, $timestamp, $layout = null, $cancelled = false) {
        $this->id = $id;
        foreach ($items as $item) {
            if ($item instanceof TransactionItem) {
                $this->items[] = $item;
            } else {
                $this->items[] = new TransactionItem($item['id'], $item['name'], $item['qty'], $item['price']);
            }
        }
        $this->total = $total;
        $this->paymentMethod = $paymentMethod;
        $this->timestamp = $timestamp;
        $this->layout = $layout ?? '';
        $this->cancelled = $cancelled ?? false;
    }
    public function getId() { return $this->id; }
    public function getItems() { return $this->items; }
    public function getTotal() { return $this->total; }
    public function getPaymentMethod() { return $this->paymentMethod; }
    public function getTimestamp() { return $this->timestamp; }
    public function getLayout() { return $this->layout; }
    public function isCancelled() { return $this->cancelled; }
    public function toArray() {
        return [
            'id' => $this->id,
            'items' => array_map(fn($i) => $i->toArray(), $this->items),
            'total' => $this->total,
            'payment_method' => $this->paymentMethod,
            'timestamp' => $this->timestamp,
            'layout' => $this->layout,
            'cancelled' => $this->cancelled
        ];
    }
}
