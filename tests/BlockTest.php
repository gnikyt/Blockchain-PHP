<?php

namespace OhMyBrew\Blockchain;

class BlockTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $this->fixture = json_decode(file_get_contents(__DIR__.'/fixtures/d6f31977b058c45a300b0ebc824b91850f3bd7907f31e5696ad6c5601b158fb4.json'), true);
    }

    /**
     * @test
     * @expectedException ArgumentCountError
     *
     * Should ensure we get an argument.
     */
    public function shouldRequireArrayOnConstruct()
    {
        $block = new Block();
    }

    /**
     * @test
     * @expectedException Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @expectedExcepionMessage The required option "difficulty" is missing.
     *
     * Should require difficulty set for mining.
     */
    public function shouldRequireDifficulty()
    {
        $block = new Block([]);
    }

    /**
     * @test
     *
     * Should generate a full block.
     */
    public function shouldGenerateFullBlock()
    {
        $blockData = $this->fixture[1];
        $block = new Block($blockData);

        $this->assertEquals($blockData['index'], $block->getIndex());
        $this->assertEquals($blockData['nonce'], $block->getNonce());
        $this->assertEquals($blockData['difficulty'], $block->getDifficulty());
        $this->assertEquals($blockData['data'], $block->getData());
        $this->assertEquals($blockData['previous_hash'], $block->getPreviousHash());
        $this->assertEquals($blockData['hash'], $block->getHash());
        $this->assertEquals($blockData['timestamp'], $block->getTimestamp());
        $this->assertEquals(null, $block->getPrevious()); // not loaded in a chain
        $this->assertEquals(true, $block->isMined());
        $this->assertInstanceOf(Block::class, $block);
    }

    /**
     * @test
     *
     * Should validate previous block.
     */
    public function shouldValidateWithPreviousBlock()
    {
        $previous = new Block($this->fixture[0]);
        $block = new Block($this->fixture[1], $previous);

        $this->assertEquals($previous, $block->getPrevious());
        $this->assertEquals($previous->getHash(), $block->getPreviousHash());
        $this->assertTrue($block->validateNonce());
    }

    /**
     * @test
     *
     * Should fill in previous block values for new block creation in chain.
     */
    public function shouldFillPreviousBlockDataOnNewBlockCreation()
    {
        $previous = new Block($this->fixture[0]);
        $block = new Block(['data' => 'Hello', 'difficulty' => 5], $previous);

        $this->assertEquals($previous, $block->getPrevious());
        $this->assertEquals($previous->getHash(), $block->getPreviousHash());
        $this->assertEquals($previous->getIndex() + 1, $block->getIndex());
    }

    /**
     * @test
     *
     * Should invalidate previous block if wrong.
     */
    public function shouldInvalidateWithWrongPreviousBlock()
    {
        $previous = new Block($this->fixture[2]);
        $block = new Block($this->fixture[1], $previous);

        $this->assertEquals($previous, $block->getPrevious());
        $this->assertNotEquals($previous->getHash(), $block->getPreviousHash());
        $this->assertFalse($block->validateNonce());
    }

    /**
     * @test
     *
     * Should encode JSON.
     */
    public function shouldEncodeJSON()
    {
        $fixture = $this->fixture[1];
        $block = new Block($fixture);

        $this->assertEquals(json_encode($fixture), json_encode($block));
    }

    /**
     * @test
     *
     * Should generate hash for block.
     */
    public function shouldGenerateHash()
    {
        $blockData = $this->fixture[0];
        $hash = $blockData['hash'];
        unset($blockData['hash']);

        $block = new Block($blockData);
        $newHash = $block->generateHash(true);

        $this->assertEquals($hash, $newHash);
        $this->assertEquals($hash, $block->getHash());
    }

    /**
     * @test
     *
     * Should mine block.
     */
    public function shouldMineBlock()
    {
        $block = new Block([
            'difficulty' => 1,
            'data'       => 'Hello World',
            'timestamp'  => 1519403271, // So we can keep the mining result constant for this data/block
        ]);

        $this->assertFalse($block->isMined());

        $block->mine();

        $this->assertEquals(3, $block->getNonce());
        $this->assertTrue($block->isMined());
    }
}
