<?php

declare(strict_types=1);

namespace Printing;

use Printing\Options;
use Printing\Printers;
use Printing\Exceptions\InvalidJobException;
use Printing\Exceptions\InvalidFileException;
use Printing\Exceptions\InvalidPrinterException;

class Client
{
    protected $file;
    protected $options;
    protected $printer;
    protected $printers;
    protected $response;
    protected $jobId;

    public function __construct(string $printer, ?Options $options = null)
    {
        $this->options = $options ?: (new Options);
        $this->printers = new Printers;

        $this->setPrinter($printer);
    }

    public function printers()
    {
        return $this->printers->all();
    }

    public function print(string $file)
    {
        $this->setFile($file);

        $command = $this->getCommand();

        $this->response = shell_exec($command);

        return $this->job();
    }

    protected function job()
    {
        preg_match('/-(\d+)/', $this->response, $matches);

        if(isset($matches[1])) {
            return new Job($this->printer . '-' . $matches[1]);
        }

        throw new InvalidJobException(sprintf("The response did not contain a job id. Response: %s", $this->response));
    }

    /**
     * Get the value of options
     */ 
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set the value of options
     *
     * @param Options $options
     * @return  self
     */ 
    public function setOptions(Options $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get the value of file
     */ 
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set the value of file
     *
     * @return  self
     */ 
    public function setFile($file)
    {
        if(!file_exists($file)) {
            throw new InvalidFileException(sprintf("The file '%s' is invalid since it does not exists.", $file));
        }

        $this->file = $file;

        return $this;
    }

    /**
     * Get the value of printer
     */ 
    public function getPrinter()
    {
        return $this->printer;
    }

    /**
     * Set the value of printer
     *
     * @return  self
     */ 
    public function setPrinter($printer)
    {
        if(!$this->printers->get($printer)) {
            throw new InvalidPrinterException(sprintf("The Printer '%s' is invalid since it does not exists.", $printer));
        }

        $this->printer = $printer;

        return $this;
    }

    public function getCommand()
    {
        return escapeshellcmd(sprintf("lp -d %s %s%s", $this->printer, $this->options->asString(), ($this->file ? ' ' . $this->file : '')));
    }

    /**
     * Get the value of response
     */ 
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set the value of response
     *
     * @return  self
     */ 
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }
}
