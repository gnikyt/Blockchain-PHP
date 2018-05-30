<?php

namespace drupol\blockchain\Miner;

use drupol\blockchain\Block\BlockInterface;
use drupol\blockchain\Blockchain\BlockchainInterface;

class Miner
{
    /**
     * @param \drupol\blockchain\Block\BlockInterface                $block
     * @param \drupol\blockchain\Blockchain\BlockchainInterface|null $blockchain
     *
     * @return \drupol\blockchain\Block\BlockInterface
     */
    public static function mine(BlockInterface $block, BlockchainInterface $blockchain = null) : BlockInterface
    {
        $nonce = 0;

        while (!self::validateNonce($block, $nonce)) {
            $nonce++;
        }

        return $block->withNonce($nonce);
    }

    /**
     * @param \drupol\blockchain\Block\BlockInterface                $block
     * @param int|null                                               $nonce
     * @param \drupol\blockchain\Blockchain\BlockchainInterface|null $blockchain
     *
     * @return bool
     */
    public static function validateNonce(
        BlockInterface $block,
        int $nonce = null,
        BlockchainInterface $blockchain = null
    ) : bool {
        $difficulty = $block->getDifficulty();
        $nonce = is_null($nonce) ? $block->getNonce() : $nonce;
        $guessHash = hash($block->getAlgo(), $nonce . $block->getPreviousHash() . $block->generateHash());

        return substr($guessHash, 0, $difficulty) === str_pad('', $difficulty, substr($nonce, -1));
    }
}
