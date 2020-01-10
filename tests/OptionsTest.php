<?php

declare(strict_types=1);

namespace Printing;

use Printing\Options;
use Printing\TestCase;
use Printing\Exceptions\InvalidOptionException;

class OptionsTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_create_new_options_class()
    {
        $this->assertInstanceOf(Options::class, new Options());
    }

    /**
     * @test
     */
    public function it_fails_accepting_invalid_options()
    {
        $this->expectException(InvalidOptionException::class);
        
        $options = new Options(['a' => 'test']);
    }

    /**
     * @test
     */
    public function it_accepts_valid_options()
    {
        $options = new Options(['c' => 'test', 'i' => 'test']);
        $this->assertNotEmpty($options->all());
    }

    /**
     * @test
     */
    public function it_returns_allowed_options()
    {
        $allowed = (new Options)->allowedOptions();
        $this->assertNotEmpty($allowed);
        $this->assertArrayHasKey('E', $allowed);
    }

    /**
     * @test
     */
    public function it_returns_allowed_common_options()
    {
        $allowed = (new Options)->commonOptions();
        $this->assertNotEmpty($allowed);
        $this->assertArrayHasKey('media', $allowed);
    }

    /**
     * @test
     */
    public function it_properly_converts_option_names()
    {
        $options = (new Options(['copies' => 2, 'o' => ['media' => 'A6', 'b' => 'a']]))->all();
        $this->assertNotEmpty($options);
        $this->assertArrayHasKey('n', $options);
    }

    /**
     * @test
     */
    public function it_creates_the_options_string()
    {
        $command = (new Options(['n' => 2, 'o' => ['media' => 'A6']]))->asString();
        
        $this->assertEquals("-n 2 -q 1 -H immediate -o media=A6", $command);
    }

    /**
     * @test
     */
    public function it_creates_the_options_string_with_custom_values()
    {
        $command = (new Options(['n' => 2, 'username' => 'test', 'o' => ['media' => 'A6']]))->asString();
        
        $this->assertEquals("-n 2 -q 1 -H immediate -U test -o media=A6", $command);
    }
}
