# BlockChain

A simple object-oriented blockchain implementation. Logic helpers for blocks and block chain included to ease development of applications.

This only provides interaction for the chain and blocks, all other logic must be implemented outside.

## Files

`Block.php` reprecents a single block. It can be created empty or details can be injected in. It contains helpers for hashing, mining, and validation.

`Blockchain.php` controls the chain of blocks. It can be created empty or a chain can be injected. It contains helpers for adding blocks, verifing blocks, comparing blocks, and more.

## Example Code

See `example/`.

+ Generating a blockchain, `php example/create_basic_chain.php {NUM_BLOCKS}` where number of NUM_BLOCKS is the number of blocks you wish to generate
+ Loading an existing blockchain into the code, `php example/load_chain.php {PATH}` where path is the JSON file created from `create_basic_chain.php`
