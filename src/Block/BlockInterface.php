<?php

namespace drupol\blockchain\Block;

use Symfony\Component\OptionsResolver\OptionsResolver;

interface BlockInterface
{
    /**
     * Get the nonce result from mining.
     *
     * @return null|int
     */
    public function getNonce() : ?int;

    /**
     * Get the difficulty for the mining.
     *
     * @return int
     */
    public function getDifficulty() : int;

    /**
     * Get the timestamp of the block creation.
     *
     * @return int
     */
    public function getTimestamp() : int;

    /**
     * Get the data for the block.
     *
     * @return string|null
     */
    public function getData() : ?string;

    /**
     * Get the hash for the block.
     *
     * @return null|string
     */
    public function getHash() : ?string;

    /**
     * Get the previous hash, if available.
     *
     * @return null|string
     */
    public function getPreviousHash() : ?string;

    /**
     * Generates a hash based on the block data.
     *
     * @param null|bool $assign Save the generation to the block
     *
     * @return string
     */
    public function generateHash(bool $assign = false) : string;

    /**
     * Check if block is mined.
     *
     * @return bool
     */
    public function isMined() : bool;

    /**
     * Serialization when calling json_encode.
     *
     * @return array
     */
    public function jsonSerialize() : array;

    /**
     * Options for creating or initiating the block.
     * We use OptionsResolver to make it easier to inject an array of data in vs
     * Typing out and remembering a list of arguments for the constructor.
     *
     * @param OptionsResolver $resolver
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver) : void;

    /**
     * @param int $nonce
     *
     * @return \drupol\blockchain\Block\BlockInterface
     */
    public function withNonce(int $nonce) : self;

    /**
     * Get the algorithm used to generate the hash.
     *
     * @return string
     */
    public function getAlgo() : string;

    /**
     * Get the state of the object.
     *
     * @return array
     */
    public function getState() : array;
}
