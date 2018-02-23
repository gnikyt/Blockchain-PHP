<?php

namespace OhMybrew\Blockchain;

use JsonSerializable;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Block implements JsonSerializable
{
    protected $state;
    private static $resolver;

    /**
     * Init a block.
     *
     * @param array|null $state    The state/options for block
     * @param Block|null $previous The previous block
     *
     * @return Block
     */
    public function __construct(?array $state, ?self $previous = null)
    {
        if (!isset(self::$resolver)) {
            self::$resolver = new OptionsResolver();
            $this->configureOptions(self::$resolver);
        }

        if (!is_null($previous)) {
            $state['previous'] = $previous;
        }

        $this->state = self::$resolver->resolve($state);

        return $this;
    }

    /**
     * Get the block index.
     *
     * @return int
     */
    public function getIndex() : int
    {
        return $this->state['index'];
    }

    /**
     * Get the nonce result from mining.
     *
     * @return null|int
     */
    public function getNonce() : ?int
    {
        return $this->state['nonce'];
    }

    /**
     * Get the difficulty for the mining.
     *
     * @return int
     */
    public function getDifficulty() : int
    {
        return $this->state['difficulty'];
    }

    /**
     * Get the timestamp of the block creation.
     *
     * @return int
     */
    public function getTimestamp() : int
    {
        return $this->state['timestamp'];
    }

    /**
     * Get the data for the block.
     *
     * @return string|null
     */
    public function getData() : ?string
    {
        return $this->state['data'];
    }

    /**
     * Get the hash for the block.
     *
     * @return null|string
     */
    public function getHash() : ?string
    {
        return $this->state['hash'];
    }

    /**
     * Get the previous hash, if available.
     *
     * @return null|string
     */
    public function getPreviousHash() : ?string
    {
        return $this->state['previous_hash'];
    }

    /**
     * Get the previous block, if available.
     *
     * @return null|Block
     */
    public function getPrevious() : ?self
    {
        return $this->state['previous'];
    }

    /**
     * Generates a hash based on the block data.
     *
     * @param null|bool $assign Save the generation to the block
     *
     * @return string
     */
    public function generateHash(?bool $assign = false) : string
    {
        // Remove hash from serialize data so we don't rehash the hash
        $data = $this->jsonSerializeData();
        unset($data['hash']);

        // Generate the hash with json_encode
        $hash = hash('sha256', json_encode($data));
        if ($assign) {
            // Assign to the state if asked to
            $this->state['hash'] = $hash;
        }

        return $hash;
    }

    /**
     * Mines a block.
     * Essentially, we take the previous nonce, and attempt to
     * create a new nonce, until the previous nonce plus the new nonce
     * combined will create a hash starting with X number of zeros in
     * the front based on difficulty.
     *
     * @return Block
     */
    public function mine() : self
    {
        $nonce = 0;
        while (!$this->validateNonce($nonce)) {
            $nonce++;
        }
        $this->state['nonce'] = $nonce;

        return $this;
    }

    /**
     * Validates an nonce.
     *
     * @param null|int $nonce The new nonce value to test
     *
     * @return bool
     */
    public function validateNonce(?int $nonce = null) : ?bool
    {
        $difficulty = $this->getDifficulty();
        $nonce = is_null($nonce) ? $this->getNonce() : $nonce;
        $previousNonce = $this->getPrevious() ? $this->getPrevious()->getNonce() : 0;
        $guessHash = hash($this->hashAlgo(), "{$previousNonce}{$nonce}");

        return substr($guessHash, 0, $difficulty) === str_pad('', $difficulty, '0');
    }

    /**
     * Check if block is mined.
     *
     * @return bool
     */
    public function isMined() : bool
    {
        return !is_null($this->getNonce());
    }

    /**
     * Serialization when calling json_encode.
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        return $this->jsonSerializeData();
    }

    /**
     * Data used for serialization and hashing.
     *
     * @return array
     */
    protected function jsonSerializeData() : array
    {
        return [
            'index'         => $this->getIndex(),
            'nonce'         => $this->getNonce(),
            'difficulty'    => $this->getDifficulty(),
            'timestamp'     => $this->getTimestamp(),
            'data'          => $this->getData(),
            'previous_hash' => $this->getPreviousHash(),
            'hash'          => $this->getHash(),
        ];
    }

    /**
     * Get the hashing algoritrum to use.
     * See http://php.net/manual/en/function.hash.php for valid types.
     *
     * @return string
     */
    protected function hashAlgo() : string
    {
        return 'sha256';
    }

    /**
     * Options for creating or initiating the block.
     * We use OptionsResolver to make it easier to inject an array of data in vs
     * Typing out and remembering a list of arguments for the constructor.
     *
     * @param OptionsResolver $resolver
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver) : void
    {
        // Defaults for some options
        $resolver->setDefault(
            'previous',
            null
        );
        $resolver->setAllowedTypes(
            'previous',
            ['null', self::class]
        );

        $resolver->setDefault(
            'previous_hash',
            function (Options $options) {
                return $options['previous'] ? $options['previous']->getHash() : null;
            }
        );
        $resolver->setAllowedTypes(
            'previous_hash',
            ['null', 'string']
        );

        $resolver->setDefault(
            'index',
            function (Options $options) {
                return $options['previous'] ? $options['previous']->getIndex() + 1 : 0;
            }
        );
        $resolver->setAllowedTypes(
            'index',
            'int'
        );

        $resolver->setDefault(
            'nonce',
            null
        );
        $resolver->setAllowedTypes(
            'nonce',
            ['null', 'int']
        );

        $resolver->setDefault(
            'timestamp',
            time()
        );
        $resolver->setAllowedTypes(
            'timestamp',
            'int'
        );

        $resolver->setRequired('difficulty');
        $resolver->setAllowedTypes(
            'difficulty',
            'int'
        );

        $resolver->setDefault(
            'data',
            null
        );
        $resolver->setAllowedTypes(
            'data',
            ['null', 'string']
        );

        $resolver->setDefault(
            'hash',
            null
        );
        $resolver->setAllowedTypes(
            'hash',
            ['null', 'string']
        );
    }
}
