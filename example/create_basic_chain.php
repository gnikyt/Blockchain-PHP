<?php
require(__DIR__.'/../vendor/autoload.php');

use OhMyBrew\Blockchain\Blockchain;
use OhMyBrew\Blockchain\Block;

// Get the number of runs
if (!isset($argv[1])) {
    throw new Exception('Missing number of blocks to generate. Use "php create_basic_chain.php {NUM_BLOCKS}"');
}
$runs = intval($argv[1]);

// Color setup
$RED="\033[0;31m";
$BLUE="\033[0;34m";
$YELLOW="\033[1;33m";
$NC="\033[0m";

// Create chain
$bc = new Blockchain();
echo "[{$RED}Blockchain created{$NC}]\n\n";

// Make X blocks
for ($i = 0; $i < $runs; $i++) {
    // Build block
    $block = $bc->buildBlock(5, "Hello World {$i}");
    echo $RED.">>> ".($i === 0 ? "GENESIS block" : "Block #{$i}") . " built{$NC}\n";

    // Mine it and create a hash
    echo "{$BLUE}Mining...{$NC}\n";
    $starttime = new DateTime();
    $block->mine()->generateHash(true);
    $timediff = $starttime->diff(new DateTime());
    echo "{$BLUE}Mined in {$timediff->format('%s')} seconds\n\tHash: {$block->getHash()}\n\tNonce: {$block->getNonce()}\n\tData: {$block->getData()}{$NC}\n";

    // Add it to the chain
    $bc->addBlock($block, true);
    echo "{$RED}Added block to chain!{$NC}\n\n";
}

// Done, output results
$bcHash = hash('sha256', json_encode($bc->getChain()));
echo "Blockchain hash is ".$bcHash." with ".intval($argv[1])." valid blocks added to the chain.\n";

// Write chain to file
file_put_contents(__DIR__."/{$bcHash}.json", json_encode($bc->getChain(), JSON_PRETTY_PRINT));
