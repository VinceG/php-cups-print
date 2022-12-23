<?php

declare(strict_types=1);

namespace Printing;

use Printing\Printers;
use Printing\TestCase;
use Tightenco\Collect\Support\Collection;

class PrintersTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_all_printers()
    {
        $printers = (new Printers)->all();
        $this->assertTrue($printers instanceof Collection);
    }

    /**
     * @test
     */
    public function it_can_find_a_printer()
    {
        $class = new Printers;
        $first = $class->all()->first();

        if(is_null($first)) {
            $this->assertEmpty($class->all()->toArray());
        } else {
            $name = $first['name'];
            
            $this->assertNotEmpty($class->get($name));
        }
    }
}
