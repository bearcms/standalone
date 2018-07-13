<?php

/*
 * Bear CMS standalone
 * https://github.com/bearcms/standalone
 * Copyright (c) Amplilabs Ltd.
 * Free to use under the MIT license.
 */

/**
 * @runTestsInSeparateProcesses
 */
class BasicsTest extends PHPUnit\Framework\TestCase
{

    /**
     * 
     */
    public function testAcquire()
    {
        $standalone = new \BearCMS\Standalone();
        $this->assertTrue($standalone instanceof \BearCMS\Standalone);
    }

}
