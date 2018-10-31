<?php

namespace App\Services;

/**
 * Class ProgressBar
 * @package App\Services
 */
class ProgressBar
{
    private $filler;

    /**
     * ProgressBar constructor.
     * @param string $filler
     */
    public function __construct(string $filler = '.')
    {
        $this->filler = $filler;
    }

    /**
     * @param int $step
     */
    public function advance(int $step = 1)
    {
        for ($i = 0; $i < $step; $i++) {
            echo $this->filler;
        }
    }
}