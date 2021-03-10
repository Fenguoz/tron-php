<?php

namespace Tron;

class Block
{
    public $blockID;
    public $block_header;

    public function __construct(string $blockID, array $block_header)
    {
        if (!strlen($blockID)) {
            throw new \Exception('blockID empty');
        }

        $this->blockID = $blockID;
        $this->block_header = $block_header;
    }
}
