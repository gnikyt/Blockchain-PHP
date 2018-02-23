<?php

namespace OhMyBrew\Blockchain;

use \Exception;
use \IteratorAggregate;
use \ArrayIterator;
use OhMyBrew\Blockchain\Block;

class Blockchain implements IteratorAggregate
{
    protected $chain;

    /**
     * Block chain creation.
     *
     * @param array|null $chain The chain data
     */
    public function __construct(?array $chain = [])
    {
        $this->chain = $chain;

        return $this;
    }

    /**
     * Get the chain.
     *
     * @return array
     */
    public function getChain() : array
    {
        return $this->chain;
    }

    /**
     * Allows for iterating through the chain.
     *
     * @return ArrayIterator
     */
    public function getIterator() : ArrayIterator
    {
        return new ArrayIterator($this->getChain());
    }

    /**
     * Gets the previous block in the chain (last or based on index).
     *
     * @param int|null $index The block index to base on
     *
     * @return Block|null
     */
    public function getPreviousBlock(?int $index = null) : ?Block
    {
        $target = is_null($index) ? count($this->getChain()) - 1 : $index - 1;
        return $target >= 0 ? $this->getChain()[$target] : null;
    }

    /**
     * Determines if two blocks are the same.
     *
     * @param Block $block1 The first block
     * @param Block $block2 The second block
     *
     * @return bool
     */
    public function isSameBlock(Block $block1, Block $block2) : bool
    {
        return $block1 === $block2;
    }

    /**
     * Determines if the new block is valid to add to the chain.
     *
     * @param Block $newBlock The new block
     *
     * @return bool
     */
    public function isValidBlock(Block $newBlock) : bool
    {
        // Grab previous block automatically if available
        $previousBlock = $this->getPreviousBlock($newBlock->getIndex());

        $previousBlockChecks = true;
        if (!is_null($previousBlock)) {
            /*
            * Non-genesis block validation...
            * Check if index is +1 of previous index
            * Check if previous hash matches hash of previous
            */
            $previousBlockChecks = ($previousBlock->getIndex() + 1) === $newBlock->getIndex() &&
                                   $previousBlock->getHash() === $newBlock->getPreviousHash() &&
                                   $newBlock->validateNonce();
        }

        /*
         * Check if hash matches hash (no tampering in-between)
         * Check if mined
         * Check if nonce is valid
         */
        $newBlockChecks = $newBlock->generateHash() === $newBlock->getHash() &&
                          $newBlock->isMined();

        return $newBlockChecks && $previousBlockChecks;
    }

    /**
     * Walks the chain and determine if it is valid.
     *
     * @return bool
     */
    public function isValid() : bool
    {
        foreach ($this as $key => $block) {
            if (!$this->isValidBlock($block)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Adds a block to the chain if it is valid.
     *
     * @param Block $block         The new block to add
     * @param bool  $validateChain To validate the entire chain or not before adding the new block
     *
     * @return Blockchain|Exception
     */
    public function addBlock(Block $block, ?bool $validateChain = false) : ?Blockchain
    {
        if (!$this->isValidBlock($block)) {
            throw new Exception('Block not valid, cannot add block to chain');
        }

        if ($validateChain && !$this->isValid()) {
            throw new Exception('Blockchain is invalid, cannot add block to chain');
        }

        $this->chain[] = $block;
        return $this;
    }

    /**
     * Simple shortcut to building a block.
     *
     * @param int    $difficulty The mining difficulty
     * @param string $data       The data to inject
     * @param string $blockClass The block class to use for creation
     *
     * @return Block
     */
    public function buildBlock(int $difficulty, string $data, ?string $blockClass = \OhMyBrew\Blockchain\Block::class) : Block
    {
        return new $blockClass([
            'difficulty' => $difficulty,
            'previous' => $this->getPreviousBlock(),
            'data' => $data
        ]);
    }
}
