<?php

/*
Copy from: http://web.archive.org/web/20090617110918/http://www.openasthra.com/c-tidbits/printing-binary-trees-in-ascii/
Source: http://web.archive.org/web/20071224095835/http://www.openasthra.com:80/wp-content/uploads/2007/12/binary_trees1.c
*/

namespace DS;

class PTree extends AVL
{
    const MAX_HEIGHT = 1000;

    private $gap = 3;
    private $rprofile = [];
    private $lprofile = [];

    public function __toString()
    {
        $this->lprofile = [];
        $this->rprofile = [];

        return $this->printTree($this->root);
    }

    private function printTree(Node $node = null)
    {
        if($node == null) return "Empty tree\n";

        $root = $this->markNodesRecursive($node);
        $root->parent_dir = 0;

        $this->computeEdgeLengths($root);

        for($i = 0; $i < $root->height && $i < self::MAX_HEIGHT; $i++) {
            $this->lprofile[$i] = PHP_INT_MAX;
        }

        $this->computeLProfile($node, 0, 0);
        $xmin = 0;
        for($i = 0; $i < $root->height && $i < self::MAX_HEIGHT; $i++) {
            $xmin = min($xmin, $this->lprofile[$i]);
        }

        $out = [];
        for($i = 0; $i < (int) $root->height; $i++) {
            $print_next = 0;
            $out[] = $this->printLevel($root, -1 * $xmin, $i, $print_next);

        }

        if($root->height >= self::MAX_HEIGHT) {
            echo "Tree is taller than expected";
        }

        return implode("\n", $out)."\n";
    }

    private function printLevel(Node $node = null, int $x, int $level, &$print_next)
    {
        if ($node == null) return '';

        $isleft = $node->parent_dir == -1;

        $line = '';
        if ($level == 0) {
            for ($i = 0; $i < ($x - $print_next - (($node->lablen - $isleft) / 2)); $i++) {
                $line .= ' ';
            }
            $print_next += $i;
            $line .= $node->key;
            $print_next += $node->lablen;

        } else if ($node->edge_length >= $level) {
            if ($node->left) {
                for ($i = 0; $i < ($x - $print_next - $level); $i++) {
                    $line .= ' ';
                }
                $print_next += $i;
                $line .= '/';
                $print_next++;
            }

            if ($node->right) {
                for ($i = 0; $i < ($x - $print_next + $level); $i++) {
                    $line .= ' ';
                }
                $print_next += $i;
                $line .= "\\";
                $print_next++;

            }
        } else {
            $line .= $this->printLevel($node->left, $x - $node->edge_length - 1, $level - $node->edge_length - 1, $print_next);
            $line .= $this->printLevel($node->right, $x + $node->edge_length + 1, $level - $node->edge_length - 1, $print_next);
        }

        return $line;
    }

    private function computeEdgeLengths(Node $node = null)
    {
        if($node == null) return null;

        $this->computeEdgeLengths($node->left);
        $this->computeEdgeLengths($node->right);

        if(!$node->left && !$node->right) {
            $node->edge_length = 0;
        } else {

            if($node->left) {
                for($i = 0; $i < $node->left->height && $i < self::MAX_HEIGHT; $i++) {
                    $this->rprofile[$i] = PHP_INT_MIN;
                }
                $this->computeRProfile($node->left, 0, 0);
                $hmin = $node->left->height;

            } else {
                $hmin = 0;

            }

            if($node->right) {
                for($i = 0; $i < $node->right->height && $i < self::MAX_HEIGHT; $i++) {
                    $this->lprofile[$i] = PHP_INT_MAX;
                }
                $this->computeLProfile($node->right, 0,0);
                $hmin = min($node->right->height, $hmin);
            } else {
                $hmin = 0 ;
            }

            $delta = 4;
            for($i = 0; $i < $hmin; $i++) {
                $delta = (int) max($delta, $this->gap + 1 + $this->rprofile[$i] - $this->lprofile[$i]);
            }

            if((($node->left && $node->left->height == 1) || ($node->right && $node->right->height == 1)) && $delta > 4 ) {
                $delta--;
            }

            $node->edge_length = (int) (($delta + 1) / 2) - 1;
        }

        $h = 1;
        if($node->left) {
            $h = max($node->left->height + $node->edge_length + 1, $h);
        }
        if($node->right) {
            $h = max($node->right->height + $node->edge_length + 1, $h);
        }

        $node->height = $h;
    }

    private function computeRProfile(Node $node = null, int $x, int $y)
    {
        if($node == null) return null;

        $notleft = $node->parent_dir != -1;
        $this->rprofile[$y] = (int) max($this->rprofile[$y], $x + (($node->lablen - $notleft) / 2));
        if($node->right) {
            for($i = 1; $i <= $node->edge_length && $y + $i < self::MAX_HEIGHT; $i++) {
                $this->rprofile[$y + $i] = (int) max($this->rprofile[$y + $i], $x + 1);
            }
        }

        $this->computeRProfile($node->left, $x - $node->edge_length - 1, $y + $node->edge_length + 1);
        $this->computeRProfile($node->right, $x + $node->edge_length + 1 , $y + $node->edge_length + 1);
    }

    /**
     * The following function fills in the lprofile array for the given tree.
     *
     * It assumes that the center of the label of the root of this tree is located at a position (x,y).
     * It assumes that the edge_length fields have been computed for this tree.
     */
    private function computeLProfile(Node $node = null, int $x, int $y)
    {
        if ($node == null) return null;

        $isleft = $node->parent_dir == -1;
        $this->lprofile[$y] = (int) min(isset($this->lprofile[$x]) ? $this->lprofile[$x] : 0, $x - (($node->lablen - $isleft) / 2));
        if ($node->left) {
            for ($i = 1; $i <= $node->edge_length && $y + $i < self::MAX_HEIGHT; $i++) {
                $this->lprofile[$y + $i] = (int) min($this->lprofile[$y + $i], $x - $i);
            }
        }

        $this->computeLProfile($node->left, $x - $node->edge_length - 1, $y + $node->edge_length + 1);
        $this->computeLProfile($node->right, $x + $node->edge_length + 1, $y + $node->edge_length + 1);
    }

    private function markNodesRecursive(Node $node = null)
    {
        if($node == null) return null;

        $node->left = $this->markNodesRecursive($node->left);
        $node->right = $this->markNodesRecursive($node->right);

        if($node->left) $node->left->parent_dir = -1;
        if($node->right) $node->right->parent_dir = 1;

        $node->lablen = strlen($node->key);

        return $node;
    }
}