<?php
require(__DIR__.'/../vendor/autoload.php');

use OhMyBrew\Blockchain\Blockchain;
use OhMyBrew\Blockchain\Block;

// Color setup
$RED="\033[0;31m";
$BLUE="\033[0;34m";
$YELLOW="\033[1;33m";
$NC="\033[0m";

// Locate the blockchain data and decode it
if (!isset($argv[1])) {
    throw new Exception('Missing blockchain to load. Use "php load_chain.php {PATH}"');
}
$bcHash = $argv[1];
$bcContents = json_decode(file_get_contents($bcHash), true);
echo "{$RED}Loading blockchain...{$NC}\n";

// Build the chain
$chain = [];
foreach ($bcContents as $key => $blockData) {
    $chain[] = new Block(array_merge(
        $blockData,
        [
            'previous' => isset($chain[$key - 1]) ? $chain[$key - 1] : null
        ]
    ));
}

// Load the chain
$bc = new Blockchain(5, $chain);
echo "{$BLUE}Inserted ".count($chain)." blocks{$NC}\n";

// Verify the chain
if ($bc->isValidChain()) {
    echo "Chain is: {$BLUE}Valid{$NC}\n";
    echo "Blockchain hash is ".hash('sha256', json_encode($chain)) . " which matches the file input\n";
} else {
    echo "Chain is: {$RED}Invalid{$NC}\n";
}
