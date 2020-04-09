<?php
$loader = require_once __DIR__ . '/../vendor/autoload.php';

$AVL = new DS\PTree();
//$AVL->dorebalancing = false;

while(true) {
    switch (readline('cmd:')) {
        case 'a':
            $map = [];
            for($i = 0; $i < 40; $i++) {
                do {
                    $rand = rand(1, 100);
                } while (isset($map[$rand]));

                $AVL->put($rand);
                echo $AVL;
                readline('press enter');
            }
            break;
        case 'q':
            die("good bye!\n");
            break;
        case 'i':
            $e = readline('elem:');
            $AVL->put($e);
            echo $AVL;
            break;
        case 't':
            $AVL->dorebalancing = !$AVL->dorebalancing;
            break;
        case 'p':
            echo $AVL;
            break;
        case 's':
            print_r($AVL);
            break;
        case 'h';
            echo "tree height: {$AVL->treeHeight()}\n";
            break;
    }
}
