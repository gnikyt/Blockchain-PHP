<?php

namespace drupol\blockchain\Blockchain;

use drupol\blockchain\Block\BlockInterface;

interface BlockchainInterface
{
    /**
     * Get the chain.
     *
     * @return \drupol\blockchain\Block\BlockInterface[]
     */
    public function getChain() : array;

    /**
     * Allows for iterating through the chain.
     *
     * @return \ArrayIterator
     */
    public function getIterator() : \ArrayIterator;

    /**
     * Gets the previous block in the chain (last or based on index).
     *
     * @param \drupol\blockchain\Block\BlockInterface $block
     *
     * @return \drupol\blockchain\Block\BlockInterface|null
     */
    public function getPreviousBlock(BlockInterface $block = null) : ?BlockInterface;

    /**
     * Determines if the new block is valid to add to the chain.
     *
     * @param \drupol\blockchain\Block\BlockInterface $newBlock
     *                                                          The new block
     *
     * @return bool
     */
    public function isValidBlock(BlockInterface $newBlock) : bool;

    /**
     * Walks the chain and determine if it is valid.
     *
     * @return bool
     */
    public function isValid() : bool;

    /**
     * Adds a block to the chain if it is valid.
     *
     * @param \drupol\blockchain\Block\BlockInterface $block         The new block to add
     * @param bool                                    $validateChain To validate the entire chain or not before
     *                                                               adding the new block
     *
     * @throws \Exception
     *
     * @return \drupol\blockchain\Blockchain\BlockchainInterface
     */
    public function addBlock(BlockInterface $block, bool $validateChain = false) : self;

    /**
     * Get last block.
     *
     * @return \drupol\blockchain\Block\BlockInterface|null
     */
    public function getLastBlock() : ?BlockInterface;

    /**
     * Simple shortcut to building a block.
     *
     * @param int    $difficulty The mining difficulty
     * @param string $data       The data to inject
     * @param string $blockClass The block class to use for creation
     *
     * @return \drupol\blockchain\Block\BlockInterface
     */
    public function buildBlock(
        int $difficulty,
        string $data,
        string $blockClass = \drupol\blockchain\Block\Block::class
    ) : BlockInterface;

    /**
     * Get the UUID.
     *
     * @return string
     */
    public function getUuid() : string;

    /**
     * Get the state.
     *
     * @return array
     */
    public function getState() : array;

    /**
     * Get the chain as array.
     *
     * @return array
     */
    public function getChainAsArray() : array;
}
