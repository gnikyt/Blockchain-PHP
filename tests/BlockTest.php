<?php

namespace drupol\blockchain;

use drupol\blockchain\Block\Block;
use drupol\blockchain\Blockchain\Blockchain;
use drupol\blockchain\Miner\Miner;

class BlockTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $this->fixture = json_decode(file_get_contents(__DIR__ . '/fixtures/ddee0846fbc411410be5bb7aa40a67bd006de87558a4318015ab36b892fd6de9.json'), true);
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

        $this->assertEquals($blockData['nonce'], $block->getNonce());
        $this->assertEquals($blockData['difficulty'], $block->getDifficulty());
        $this->assertEquals($blockData['data'], $block->getData());
        $this->assertEquals($blockData['hash'], $block->getHash());
        $this->assertEquals($blockData['timestamp'], $block->getTimestamp());
        $this->assertEquals('200e9d09e2e08ae372bce8d7270229d0e0335aee5ad4beaf0ca2fcd018b3a71acaa7745230a7deffbd1a8c3caa6cf6e37a7f718ff68ff6e6330cd15276eb9012', $block->getPreviousHash());
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
        $bc = new Blockchain();

        $genesis = new Block($this->fixture[0]);
        $block = new Block($this->fixture[1], $genesis);

        $this->assertEquals($genesis->getHash(), $block->getPreviousHash());
        $this->assertTrue(Miner::validateNonce($block));
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

        $this->assertEquals($previous->getHash(), $block->getPreviousHash());
    }

    /**
     * @test
     *
     * Should invalidate previous block if wrong.
     */
    public function shouldInvalidateWithWrongPreviousBlock()
    {
        $bc = new Blockchain();

        $previous = new Block($this->fixture[2]);
        $block = new Block($this->fixture[1], $previous);

        $this->assertEquals($previous->getHash(), $block->getPreviousHash());
        $this->assertFalse(Miner::validateNonce($block));
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

        $this->assertEquals(json_encode($fixture), json_encode($block->getState()));
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
            'timestamp'  => 1519403271,
        ]);

        $this->assertFalse($block->isMined());

        $blockchain = new Blockchain();
        $block = Miner::mine($block, $blockchain);

        $this->assertEquals(22, $block->getNonce());
        $this->assertTrue($block->isMined());
    }
}
