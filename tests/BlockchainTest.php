<?php

namespace drupol\blockchain;

use drupol\blockchain\Block\Block;
use drupol\blockchain\Blockchain\Blockchain;
use drupol\blockchain\Miner\Miner;
use ReflectionClass;

class BlockchainTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $this->fixture = json_decode(file_get_contents(__DIR__ . '/fixtures/ddee0846fbc411410be5bb7aa40a67bd006de87558a4318015ab36b892fd6de9.json'), true);
    }

    /**
     * @test
     *
     * Should require initial difficulty for chain.
     */
    public function shouldCreateChain()
    {
        $chain = new Blockchain();

        $this->assertInstanceOf(Blockchain::class, $chain);
    }

    /**
     * @test
     *
     * Should be traversable.
     */
    public function shouldBeTraversable()
    {
        $chain = new Blockchain();

        $this->assertInstanceOf(\Traversable::class, $chain);
    }

    /**
     * @test
     *
     * Chain should be valid without any blocks.
     */
    public function shouldBeValidChainEvenWithoutBlocks()
    {
        $chain = new Blockchain();

        $this->assertTrue($chain->isValid());
    }

    /**
     * @test
     *
     * Should create block.
     */
    public function shouldCreateBlock()
    {
        $chain = new Blockchain();
        $block = $chain->buildBlock(5, 'Hello World');

        $this->assertInstanceOf(Block::class, $block);
    }

    /**
     * @test
     * @expectedException \Exception
     * @exceptedExceptionMessage Block not valid, cannot add block to chain
     *
     * Should not add block to chain who hasnt been mined or invalid.
     */
    public function shouldNotAddInvalidBlockToChain()
    {
        $chain = new Blockchain();
        $block = $chain->buildBlock(5, 'Hello World');
        $chain->addBlock($block);
    }

    /**
     * @test
     *
     * Should add block if valid block.
     */
    public function shouldAddValidBlockToChain()
    {
        $chain = new Blockchain();

        $block = $chain->buildBlock(1, 'Hello World');

        $blockchain = new Blockchain();
        $block = Miner::mine($block, $blockchain);

        $block->generateHash(true);

        $chain->addBlock($block, true);

        $this->assertEquals(1, count($chain->getChain()));
        $this->assertEquals($block, $chain->getChain()[0]);
        $this->assertTrue($chain->isValid());
    }

    /**
     * @test
     *
     * Should validate chain.
     */
    public function shouldValidateChain()
    {
        $chain = new Blockchain();

        $genesis = new Block($this->fixture[0]);
        $second = new Block(
            $this->fixture[1],
            $genesis
        );

        $chain->addBlock($genesis);
        $chain->addBlock($second);

        $this->assertTrue($chain->isValid());
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage Blockchain is invalid, cannot add block to chain
     *
     * Should invalidate chain (tampering maybe?)
     */
    public function shouldInvalidateChain()
    {
        // Add 3 blocks
        $chain = new Blockchain();

        $genesis = new Block($this->fixture[0]);
        $chain->addBlock($genesis);

        $first = new Block(
            $this->fixture[1],
            $genesis
        );
        $chain->addBlock($first);

        $second = new Block(
            $this->fixture[2],
            $first
        );
        $chain->addBlock($second);

        // So we can mod the block state
        $class = new ReflectionClass(Block::class);
        $property = $class->getProperty('state');
        $property->setAccessible(true);

        // Mod the state of the third block
        $block3 = $chain->getChain()[2];
        $property->setValue(
            $block3,
            array_merge(
                $block3->getState(),
                ['previous' => '37783783783']
            )
        );

        $newblock = new Block(
            $this->fixture[3],
            new Block($this->fixture[2])
        );

        $chain->addBlock($newblock, true);
    }

    /**
     * @test
     *
     * Should confirm blocks are the same.
     */
    public function shouldConfirmBlocksAreTheSame()
    {
        $chain = new Blockchain();

        $block = $chain->buildBlock(1, 'Hello World');
        $block = Miner::mine($block, $chain);
        $block->generateHash(true);

        $chain->addBlock($block, true);

        $this->assertEquals($chain->getChain()[0], $block);
    }

    /**
     * @test
     *
     * Input of chain should match output
     */
    public function shouldMatchInputAndOutput()
    {
        $chain = [];
        foreach ($this->fixture as $key => $blockData) {
            $chain[] = new Block(array_merge(
                $blockData,
                [
                    'previous' => isset($chain[$key - 1]) ? $chain[$key - 1]->getHash() : null,
                ]
            ));
        }

        $bc = new Blockchain(['chain' => $chain]);

        $this->assertEquals(
            '02b74a2b78914869d3b6974d8b2bb6609b768ec918c72f67c3180d2088a8c034a0fdc544d9c817e7f6122373ee0aaa12e088d0fd9e2be0ed22c96daa3abc5943',
            hash('sha512', json_encode($bc->getChainAsArray()))
        );
    }
}
