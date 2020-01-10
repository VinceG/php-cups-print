<?php

declare(strict_types=1);

namespace Printing;

use Printing\Client;
use Printing\Options;
use Printing\Printers;
use Printing\TestCase;
use Printing\Exceptions\InvalidFileException;
use Printing\Exceptions\InvalidPrinterException;

class ClientTest extends TestCase
{
    protected $printers;
    protected $printer;
    protected $file;

    protected function setUp(): void
    {
        $this->printers = new Printers;
        $this->printer = $this->printers->all()->first();
        $this->file = '/tmp/print_test_file';

        file_put_contents($this->file, 'test');
    }

    protected function tearDown(): void
    {
        if(file_exists($this->file)) {
            unlink($this->file);
        }
    }

    /**
     * @test
     */
    public function it_can_create_new_client()
    {
        $this->assertInstanceOf(Client::class, new Client($this->printer['name']));
    }

    /**
     * @test
     */
    public function it_fails_on_invalid_printer()
    {
        $this->expectException(InvalidPrinterException::class);

        new Client('__INVALID__');
    }

    /**
     * @test
     */
    public function it_accepts_options()
    {
        $this->assertInstanceOf(Client::class, new Client($this->printer['name'], new Options));
    }

    /**
     * @test
     */
    public function it_constructs_command()
    {
        $client = new Client($this->printer['name']);
        $this->assertEquals("lp -d Brother_MFC_L8850CDW -n 1 -q 1 -H immediate", $client->getCommand());
    }

    /**
     * @test
     */
    public function it_requires_valid_file_to_print()
    {
        $this->expectException(InvalidFileException::class);

        $client = new Client($this->printer['name']);
        $client->print('__INVALID__');
    }

    /**
     * @test
     */
    public function it_sends_print_job()
    {
        $client = new Client($this->printer['name']);
        $job = $client->print($this->file);
        $command = $client->getCommand();

        $this->assertEquals('printing', $job->status());
        $this->assertFalse($job->isComplete());

        sleep(60);

        $this->assertEquals('completed', $job->status());
        $this->assertTrue($job->isComplete());
    }
}
