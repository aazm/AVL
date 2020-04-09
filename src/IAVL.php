<?php

namespace DS;

interface IAVL
{
    public function put(int $key): ?Node;

    public function get(int $key): ?Node;

    public function delete(int $key): ?Node;

    public function getKeyOrParent(int $key): ?Node;

    public function successor(int $key): ?Node;

    public function minSubTree(Node $node = null): ?Node;

    public function maxSubTree(Node $node = null): ?Node;

    public function compareTo($key1, $key2): int;

    public function balance(Node $node): Node;

    public function height(Node $node): int;

    public function rr(Node $node): void;

    public function rl(Node $node): void;

    public function rrl(Node $node): void;

    public function rlr(Node $node): void;

}