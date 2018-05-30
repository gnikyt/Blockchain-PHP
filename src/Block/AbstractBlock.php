<?php

namespace drupol\blockchain\Block;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractBlock implements BlockInterface
{
    /**
     * @var mixed[]
     */
    protected $state;

    /**
     * @var \Symfony\Component\OptionsResolver\OptionsResolver
     */
    private static $resolver;

    /**
     * AbstractBlock constructor.
     *
     * @param array                                        $state
     * @param \drupol\blockchain\Block\BlockInterface|null $previous
     */
    public function __construct(array $state, BlockInterface $previous = null)
    {
        if (!isset(self::$resolver)) {
            self::$resolver = new OptionsResolver();
            $this->configureOptions(self::$resolver);
        }

        if (!is_null($previous)) {
            $state['previous'] = $previous->getHash();
        }

        $this->state = self::$resolver->resolve($state);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getState() : array
    {
        return $this->state;
    }

    /**
     * {@inheritdoc}
     */
    public function getNonce() : ?int
    {
        return $this->state['nonce'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDifficulty() : int
    {
        return $this->state['difficulty'];
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp() : int
    {
        return $this->state['timestamp'];
    }

    /**
     * {@inheritdoc}
     */
    public function getData() : ?string
    {
        return $this->state['data'];
    }

    /**
     * {@inheritdoc}
     */
    public function getHash() : ?string
    {
        return $this->state['hash'];
    }

    /**
     * {@inheritdoc}
     */
    public function getPreviousHash() : ?string
    {
        return $this->state['previous'];
    }

    /**
     * {@inheritdoc}
     */
    public function generateHash(bool $assign = false) : string
    {
        // Generate the hash
        $hash = hash(
            $this->getAlgo(),
            json_encode(
                array_diff_key(
                    $this->jsonSerializeData(),
                    ['hash' => 'hash', 'nonce' => 'nonce']
                )
            )
        );

        if (true === $assign) {
            // Assign to the state if asked to
            $this->state['hash'] = $hash;
        }

        return $hash;
    }

    /**
     * {@inheritdoc}
     */
    public function isMined() : bool
    {
        return null !== $this->getNonce();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize() : array
    {
        return $this->jsonSerializeData();
    }

    /**
     * {@inheritdoc}
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
            ['null', 'string']
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
        $resolver->setDefault(
            'algo',
            'sha512'
        );
        $resolver->setAllowedTypes(
            'algo',
            ['string']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAlgo() : string
    {
        return $this->state['algo'];
    }

    /**
     * {@inheritdoc}
     */
    public function withNonce(int $nonce) : BlockInterface
    {
        $clone = clone $this;
        $clone->state['nonce'] = $nonce;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    protected function jsonSerializeData() : array
    {
        return [
            'nonce'         => $this->getNonce(),
            'difficulty'    => $this->getDifficulty(),
            'timestamp'     => $this->getTimestamp(),
            'data'          => $this->getData(),
            'previous'      => $this->getPreviousHash(),
            'hash'          => $this->getHash(),
            'algo'          => $this->getAlgo(),
        ];
    }
}
