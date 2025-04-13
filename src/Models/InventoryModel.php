<?php

class Inventory {
    private $id;
    private $userId;
    private $cardId;

    public function __construct($id, $userId, $cardId) {
        $this->id = $id;
        $this->userId = $userId;
        $this->cardId = $cardId;
    }
}