<?php

namespace DS;

class AVL implements IAVL {

    public $dorebalancing = true;

    /** @var \DS\Node */
    protected $root;

    /**
     * O(1)
     *
     * @param $key1
     * @param $key2
     * @return int
     */
    public function compareTo($key1, $key2): int
    {
        if($key1 > $key2) return 1;
        if($key1 == $key2) return 0;
        if($key1 < $key2) return -1;
    }

    /**
     * O(h)
     *
     * @param int $key
     * @return Node|null
     */
    public function put(int $key): ?Node
    {
        $pnode = $this->getKeyOrParent($key);
        if($pnode == null) return $this->root = new Node($key);

        //we've found key inside tree and we're just returning it in this case
        if($pnode->key == $key) return $pnode;

        //we're about to add child node
        $cmp = $this->compareTo($pnode->key, $key);

        if($cmp > 0) return $this->balance($pnode->left = new Node($key, $pnode));
        if($cmp < 0) return $this->balance($pnode->right = new Node($key, $pnode));
    }

    /**
     * Get node if exists or return it's closest parent
     * O(h)
     *
     * @param int $key
     * @return Node|null
     */
    public function getKeyOrParent(int $key): ?Node
    {
        if($this->root == null) return null;

        /** @var \DS\Node $node */
        $node = $this->root;
        while ($node != null && $node->key != $key) {
            $pnode = $node;
            $cmp = $this->compareTo($node->key, $key);
            if($cmp < 0) $node = $node->right;
            if($cmp > 0) $node = $node->left;
        }

        // this is the case when we've found key in the tree
        // cause we are out of while loop and node is not null
        // the difference between this part of the code and get method which is a part of interface
        // is that here we're returning:
        //                  a) null if the tree is EMPTY
        //                  b) parent node if the key WAS NOT FOUND
        //                  c) node if the key WAS FOUND
        if($node) $pnode = $node;

        return $pnode;
    }

    /**
     * @param int $key
     * @return Node|null
     */
    public function get(int $key): ?Node
    {
        if($this->root == null) return null;

        $node = $this->root;
        while($node != null && $node->key != $key) {
            $cmp = $this->compareTo($node->key, $key);
            if($cmp < 0) $node = $node->left;
            if($cmp > 0) $node = $node->right;
        }

        return $node;
    }

    public function minSubTree(Node $node = null): ?Node
    {
        if($node == null) $node = $this->root;
        if($node == null) return null;
        while($node->left != null) $node = $node->left;

        return $node;
    }

    public function maxSubTree(Node $node = null): ?Node
    {
        if($node == null) $node = $this->root;
        if($node == null) return null;
        while($node->right != null) $node = $node->right;

        return $node;
    }
    /**
     * Successor is the value which is going after the given key
     * Testator is the term which describes the person who is giving something to he's successor
     *
     * @param int $key
     * @return Node|null
     */
    public function successor(int $key): ?Node
    {
        /** @var \DS\Node $testator */
        $testator = $this->get($key);
        // if key was not in the tree than I assume we can't find successor
        if($testator == null) return null;
        // if testator's right tree is not empty then successor is there.
        if($testator->right != null) return $this->minSubTree($testator->right);
        // otherwise we're going to go up in the tree and find first node in the left subtree
        $pnode = $testator->parent;
        while($pnode != null && $pnode->left->key != $testator->key) {
            $testator = $pnode;
            $pnode = $testator->parent;
        }

        return $pnode;
    }

    /**
     * During deletion we need to consider 3 cases
     * 1. Node is a leaf and has no children -> node can be removed without any additional actions
     * 2. Node has only one child -> this child should be linked to node's parent
     * 3. Node has 2 children then the node should be replaced with it's successor and successor should be deleted instead
     *
     *        A -           A -           A -              A -
     *      /             /  \             \             /  \
     *     B             B    C             B           B   [C] - successor
     *                       / \
     *        successor -  [D]   E
     *                       \
     *                       F
     * @param int $key
     * @return null
     */
    public function delete(int $key): ?Node
    {
        $node = $this->get($key);
        if($node == null) return null;

        $hasTwoChildren = $node->left && $node->right;

        // we are assured that successor call will return NOT NULL because node has 2 children
        if($hasTwoChildren) $removable = $this->successor($node->key);
        else $removable = $node;

        // we will take left sub-tree if exists and that's means that removable is not successor
        // otherwise we will take right sub-tree: successor or single child
        $child = $removable->left ? $removable->left : $removable->right;

        if($child) $child->parent = $removable->parent;

        if($removable->parent == null) $this->root = $child;
        elseif($removable->parent->left == $removable) $removable->parent->left = $child;
        elseif($removable->parent->right == $removable) $removable->parent->right = $child;

        // if we were removing successor than we need to replace node's key with successor's key
        if($this->compareTo($node->key, $removable->key) !== 0) {
            $node->key = $removable->key;
        }

        return $removable;

    }

    public function balance(Node $node): Node
    {
        if(!$this->dorebalancing) return $node;

        $current = $node;
        while($current != null) {
            $balance = $this->nodeBalance($current);
            if(abs($balance) > 1) {
                if($balance < -1) {
                    if($this->height($current->left->left) > $this->height($current->left->right)) $this->rr($current);
                    else $this->rlr($current);
                }
                if($balance > 1) {
                    if($this->height($current->right->right) > $this->height($current->right->left)) $this->rl($current);
                    else $this->rrl($current);
                }
            }

            $current = $current->parent;
        }

        return $node;
    }

    public function nodeBalance(Node $node): int
    {
        return $node->balance = $this->height($node->right) - $this->height($node->left);
    }

    public function height(Node $node = null): int
    {
        if($node == null) return 0;

        $queue = new \SplQueue();
        $queue->enqueue([$node, 1]);

        $max = 0;
        while(!$queue->isEmpty()) {

            list($current, $height) = $queue->dequeue();

            $max = $max > $height ? $max : $height;

            if($current->left) $queue->enqueue([$current->left, $height + 1]);
            if($current->right) $queue->enqueue([$current->right, $height + 1]);
        }

        return $max;
    }

    /**
     *  Right rotate around the base vertex
     *
     *        B(*)                  A
     *       /  \                  / \
     *      A    F                C   B(*)
     *     / \      =>           /   / \
     *    C  D                  E   D  F
     *   /
     *  E
     *
     * @param Node $b
     */
    public function rr(Node $b):void
    {
        $a = $b->left;

        //connect D sub-tree to B
        $b->left = $a->right;
        if($a->right) $a->right->parent = $b;

        //connect A to B's parent
        $a->parent = $b->parent;
        if($b->parent == null) {
            $this->root = $a;
        } elseif($b->parent->right == $b) {
            $b->parent->right = $a;
        } elseif($b->parent->left == $b) {
            $b->parent->left = $a;
        }

        // connect B to A as right sub-tree
        $a->right = $b;
        $b->parent = $a;

    }

    /**
     * Left rotate around the base vertex
     *
     *      A*                    C
     *     / \                   / \
     *    B   C                 A*  E
     *       / \    =>         /\    \
     *      D   E             B  D    F
     *           \
     *            F
     *
     * @param Node $base
     */
    public function rl(Node $a): void
    {
        $c = $a->right;

        //connect D to A
        $a->right = $c->left;
        if($c->left) $c->left->parent = $a;

        //connect C to A's parent
        $c->parent = $a->parent;
        if($a->parent == null) {
            $this->root = $c;
        } elseif($a->parent->left == $a) {
            $a->parent->left = $c;
        } elseif($a->parent->right == $a) {
            $a->parent->right = $c;
        }

        //connect A to C
        $c->left = $a;
        $a->parent = $c;
    }

    /**
     *
     *      B*                  B*                      E
     *     / \                 / \                     / \
     *    A   C               E   C                   A   B*
     *   / \        =>       /\         =>          /    / \
     *  D  E                A  F                   D    F  C
     *      \              /
     *      F              D
     *
     * @param Node $b
     */
    public function rlr(Node $b): void
    {
        $this->rl($b->left);
        $this->rr($b);
    }

    /**
     *
     *         B*               B*                  E
     *        /  \              / \                /\
     *       A   C             A   E              B  F
     *          / \     =>        /\     =>     / \   \
     *         D   E             C  F          A   C   D
     *             \            /
     *              F          D
     *
     *
     * @param Node $b
     */
    public function rrl(Node $b): void
    {
        $this->rr($b->right);
        $this->rl($b);
    }

    public function treeHeight() {
        return $this->height($this->root);
    }
}
