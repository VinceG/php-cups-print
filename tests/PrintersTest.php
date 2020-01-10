<?php

declare(strict_types=1);

namespace Printing;

use Printing\Printers;
use Printing\TestCase;
use Printing\Exceptions\InvalidOptionException;

class PrintersTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_all_printers()
    {
        $printers = (new Printers)->all();
        $this->assertNotEmpty($printers);
    }

    /**
     * @test
     */
    public function it_can_find_a_printer()
    {
        $class = new Printers;
        $name = $class->all()->first()['name'];
        
        $this->assertNotEmpty($class->get($name));
    }
}
