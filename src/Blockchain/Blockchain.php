<?php

namespace drupol\blockchain\Blockchain;

use ArrayIterator;
use drupol\blockchain\Block\BlockInterface;
use drupol\blockchain\Miner\Miner;
use Exception;
use IteratorAggregate;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Blockchain implements BlockchainInterface, IteratorAggregate
{
    /**
     * @var \Symfony\Component\OptionsResolver\OptionsResolver
     */
    private static $resolver;

    /**
     * @var mixed[]
     */
    private $state;

    /**
     * Blockchain constructor.
     *
     * @param array $state
     */
    public function __construct(array $state = [])
    {
        if (!isset(self::$resolver)) {
            self::$resolver = new OptionsResolver();
            $this->configureOptions(self::$resolver);
        }

        $this->state = self::$resolver->resolve($state);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefault(
            'chain',
            []
        );
        $resolver->setAllowedTypes(
            'chain',
            ['array']
        );

        $resolver->setDefault(
            'uuid',
            uniqid(self::class, true)
        );
        $resolver->setAllowedTypes(
            'uuid',
            ['string']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getUuid() :string
    {
        return $this->state['uuid'];
    }

    /**
     * {@inheritdoc}
     */
    public function getChain() : array
    {
        return $this->state['chain'];
    }

    /**
     * {@inheritdoc}
     */
    public function getChainAsArray() :array
    {
        return array_map(function (BlockInterface $block) {
            return $block->getState();
        }, $this->getChain());
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator() : ArrayIterator
    {
        return new ArrayIterator($this->getChain());
    }

    /**
     * {@inheritdoc}
     */
    public function getPreviousBlock(BlockInterface $block = null) : ?BlockInterface
    {
        if (null === $block) {
            return $block;
        }

        foreach ($this->getChain() as $candidate) {
            if ($block->getPreviousHash() === $candidate->getHash()) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param string $hash
     *
     * @return \drupol\blockchain\BlockInterface|null
     */
    public function getBlock(string $hash) : ?BlockInterface
    {
        foreach ($this->getChain() as $candidate) {
            if ($candidate->getHash() === $hash) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isValidBlock(BlockInterface $newBlock) : bool
    {
        $previousBlockChecks = true;

        // Grab previous block automatically if available
        $previousBlock = $this->getPreviousBlock($newBlock);
        if (!is_null($previousBlock)) {
            /*
            * Non-genesis block validation...
            * Check if index is +1 of previous index
            * Check if previous hash matches hash of previous
            */
            $previousBlockHash = $previousBlock->getHash();
            $newBlockPreviousHash = $newBlock->getPreviousHash() ?
                $newBlock->getPreviousHash() :
                $this->getLastBlock()->getHash();

            $previousBlockChecks = ($previousBlockHash === $newBlockPreviousHash) &&
                Miner::validateNonce($newBlock);
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
     * {@inheritdoc}
     */
    public function isValid() : bool
    {
        foreach ($this->getChain() as $key => $block) {
            if (!$this->isValidBlock($block)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function addBlock(BlockInterface $block, bool $validateChain = false) : BlockchainInterface
    {
        if ($validateChain && !$this->isValid()) {
            throw new Exception('Blockchain is invalid, cannot add block to chain');
        }

        if (!$this->isValidBlock($block)) {
            throw new Exception('Block not valid, cannot add block to chain');
        }

        $this->state['chain'][] = $block;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastBlock() : ?BlockInterface
    {
        $chain = $this->getChain();

        if ([] === $chain) {
            return null;
        }

        return end($chain);
    }

    /**
     * {@inheritdoc}
     */
    public function buildBlock(
        int $difficulty,
        string $data,
        string $blockClass = \drupol\blockchain\Block\Block::class
    ) : BlockInterface {
        return new $blockClass([
            'difficulty' => $difficulty,
            'previous'   => $this->getLastBlock() ? $this->getLastBlock()->getHash() : null,
            'data'       => $data,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getState(): array
    {
        return $this->state;
    }
}
