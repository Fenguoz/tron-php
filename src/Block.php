<?php

namespace Tron;

class Block
{
    public $blockID;
    public $block_header;
    public $transactions;

    public function __construct(string $blockID, array $block_header, array $transactions = [])
    {
        if (!strlen($blockID)) {
            throw new \Exception('blockID empty');
        }

        $this->blockID = $blockID;
        $this->block_header = $block_header;
        $this->transactions = $transactions;
    }
}
