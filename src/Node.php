<?php

namespace DS;

class Node {
    public $key;

    public $left;
    public $right;
    public $parent;

    public $balance;

    //-1=I am left, 0=I am root, 1=right
    public $parent_dir = 0;
    public $lablen = 0;
    public $edge_length;
    public $height;

    public function __construct($key, Node $parent = null)
    {
        $this->key = $key;
        $this->parent = $parent;
        $this->balance = 0;
    }
}
