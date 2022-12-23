<?php

declare(strict_types=1);

namespace Printing;

class Printers
{
    protected $printers = [];

    public function __construct()
    {
        if (0 === count($this->printers)) {
            $this->buildPrinters();
        }

        return $this->printers;
    }

    public function all()
    {
        return collect($this->printers);
    }

    public function get($name)
    {
        return $this->printers->where('name', $name)->first();
    }

    protected function buildPrinters()
    {
        $raw = shell_exec("lpstat -p & > /dev/null");

        if(gettype($raw) === 'NULL') {
            return $this;
        }

        $this->printers = collect(explode("\n", $raw))
            ->reject(function ($value) {
                return empty($value) || stripos($value, 'unknown') !== false || stripos($value, 'looking') !== false;
            })
            ->reject(function($value) {
                preg_match("/printer ([\w\d\-\_]+) .*(enabled|disabled).*/i", $value, $matches);

                return !isset($matches[1]);
            })
            ->map(function ($value) {
                preg_match("/printer ([\w\d\-\_]+) .*(enabled|disabled).*/i", $value, $matches);
                
                return [
                    'name' => $matches[1],
                    'status' => $matches[2]
                ];
            });

        return $this;
    }
}
