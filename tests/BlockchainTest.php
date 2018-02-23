<?php

namespace OhMyBrew\Blockchain;

use \ReflectionClass;

class BlockchainTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $this->fixture = json_decode(file_get_contents(__DIR__.'/fixtures/d6f31977b058c45a300b0ebc824b91850f3bd7907f31e5696ad6c5601b158fb4.json'), true);
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
        $block->mine();
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
        $chain->addBlock(new Block($this->fixture[0]));
        $chain->addBlock(
            new Block(
                $this->fixture[1],
                new Block($this->fixture[0])
            )
        );

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
        $chain->addBlock(new Block($this->fixture[0]));
        $chain->addBlock(
            new Block(
                $this->fixture[1],
                new Block($this->fixture[0])
            )
        );
        $chain->addBlock(
            new Block(
                $this->fixture[2],
                new Block($this->fixture[1])
            )
        );

        // So we can mod the block state
        $class = new ReflectionClass(Block::class);
        $property = $class->getProperty('state');
        $property->setAccessible(true);

        // Mod the state of the third block
        $block3 = $chain->getChain()[2];
        $property->setValue(
            $block3,
            array_merge(
                $property->getValue($block3),
                ['previous_hash' => '37783783783']
            )
        );

        // Try to add the 4th block and validate the chain while adding
        $chain->addBlock(
            new Block(
                $this->fixture[3],
                new Block($this->fixture[2])
            ),
            true // Validate chain
        );
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
        $block->mine();
        $block->generateHash(true);

        $chain->addBlock($block, true);

        $this->assertTrue($chain->isSameBlock($chain->getChain()[0], $block));
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
                    'previous' => isset($chain[$key - 1]) ? $chain[$key - 1] : null
                ]
            ));
        }

        $bc = new Blockchain($chain);

        $this->assertEquals(
            'd6f31977b058c45a300b0ebc824b91850f3bd7907f31e5696ad6c5601b158fb4',
            hash('sha256', json_encode($chain))
        );
    }
}
