<?php

declare(strict_types=1);

namespace Printing;

use Printing\Exceptions\InvalidOptionException;

class Options
{
    protected $options = [];

    protected $generalOptions = [
        'E' => [
            'options' => ['E', 'encryption'],
            'description' => 'Forces encryption when connecting to the server',
            'default' => null,
        ],
        'U' => [
            'options' => ['U', 'Username', 'username'],
            'description' => 'Specifies the username to use when connecting to the server',
            'default' => null
        ],
        'c' => [
            'options' => ['c', 'backwardsCompatibility'],
            'description' => 'This option is provided for backwards-compatibility only. On systems tha support it, this option forces the print file to be copied to the spool directory before  printing. In CUPS, print files  are always sent to the scheduler via IPP which has the same effect.',
            'default' => null
        ],
        'd' => [
            'options' => ['d', 'destination'],
            'description' => 'Prints files to the named printer',
            'default' => null
        ],
        'h' => [
            'options' => ['h', 'hostname'],
            'description' => 'Chooses an alternate server',
            'default' => null
        ],
        'i' => [
            'options' => ['i', 'job-id'],
            'description' => 'Specifies an existing job to modify',
            'default' => null
        ],
        'm' => [
            'options' => ['m'],
            'description' => 'Sends an email when the job is completed',
            'default' => null
        ],
        'n' => [
            'options' => ['n', 'copies', 'numCopies'],
            'description' => 'Sets the number of copies to print from 1 to 100',
            'default' => 1
        ],
        'o' => [
            'options' => ['o'],
            'description' => '"name=value [name=value ...]" Sets one or more job options',
            'default' => null
        ],
        'q' => [
            'options' => ['q', 'priority'],
            'description' => 'Sets the job priority from	1 (lowest) to 100 (highest). The default priority is 50',
            'default' => 1
        ],
        's' => [
            'options' => ['s'],
            'description' => 'Do not report the resulting job IDs (silent mode.)',
            'default' => null
        ],
        't' => [
            'options' => ['t', 'name'],
            'description' => 'Sets the job name',
            'default' => null
        ],
        'H' => [
            'options' => ['H', 'when'],
            'description' => 'Specifies  when  the  job  should be printed. A value of immediate will print the file immediately, a value of hold will hold the job indefinitely, and a time value (HH:MM) will hold the job until the specified time. Use a value of resume with the -i option to resume a  held job.  Use a value of restart with the -i option to restart a completed job.',
            'default' => 'immediate'
        ],
        'p' => [
            'options' => ['P', 'page-list'],
            'description' => 'page-list Specifies which pages to print in the document. The list can  contain a list of numbers and ranges (#-#) separated by commas (e.g. 1,3-5,16).',
            'default' => null
        ]
    ];

    protected $commonOptions = [
        'media' => [
            'title' => 'Media Type',
            'description' => 'Sets  the  page  size  to size. Most printers support at least the size names "a4", "letter", and "legal".',
            'default' => 'Letter'
        ],
        'orientation-requested' => [
            'title' => 'Print Orientation',
            'description' => '4 = Prints the job in landscape (rotated 90 degrees counter-clockwise), 5 = Prints the job in landscape (rotated 90 degrees clockwise), 6 = Prints the job in reverse portrait (rotated 180 degrees)',
            'default' => null
        ],
        'sides' => [
            'title' => 'Sides',
            'description' => 'Prints on one or two sides of the paper. The value "two-sided-long-edge" is normally used when printing portrait (unrotated) pages, while "two-sided-short-edge" is used for landscape pages.',
            'default' => null
        ],
        'fitplot' => [
            'title' => 'Scales the print file to fit on the page',
            'description' => '',
            'default' => null
        ],
        'page-bottom' => [
            'title' => 'Bottom Page Margin',
            'description' => 'Sets the page margins when printing text files. The values are in points - there are 72 points to the inch',
            'default' => null
        ],
        'page-left' => [
            'title' => 'Left Page Margin',
            'description' => 'Sets the page margins when printing text files. The values are in points - there are 72 points to the inch',
            'default' => null
        ],
        'page-right' => [
            'title' => 'Right Page Margin',
            'description' => 'Sets the page margins when printing text files. The values are in points - there are 72 points to the inch',
            'default' => null
        ],
        'page-top' => [
            'title' => 'Top Page Margin',
            'description' => 'Sets the page margins when printing text files. The values are in points - there are 72 points to the inch',
            'default' => null
        ]
    ];

    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    public function setOptions(array $options)
    {
        foreach($options as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function set($key, $value): Options
    {
        // Validate that the option key is valid
        $this->validateOption($key, $value);

        $this->options[$key] = $value;

        return $this;
    }

    public function get($key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }

    public function all()
    {
        $defaultOptions = collect($this->generalOptions)->filter(function($value) {
            return !is_null($value['default']);
        })->mapWithKeys(function($value, $key) {
            return [$key => $value['default']];
        });

        $options = collect($this->options)->mapWithKeys(function($value, $key) {
            return [$this->findGeneralOptionKey($key) => $value];
        });

        return $defaultOptions->merge($options);
    }

    public function asString()
    {
        $cmd = $this->all()->map(function($value, $key) {
            return $this->optionAsString($key, $value);
        })
        ->implode(' ');

        return $cmd;
    }

    public function allowedOptions()
    {
        return $this->generalOptions;
    }

    public function commonOptions()
    {
        return $this->commonOptions;
    }

    public function validateOption($key, $value)
    {
        $exists = collect($this->generalOptions)->pluck('options')->flatten()->contains($key);

        if(!$exists) {
            throw new InvalidOptionException(sprintf("The %s option is invalid", $key));
        }

        // Validate the o Option
        if($key == 'o') {
            // Make sure it's an array of key value pairs
            if(!is_array($value)) {
                throw new InvalidOptionException("The 'o' options must be an array of key value pairs.");
            }
        }
    }

    protected function findGeneralOptionKey($optionKey)
    {
        return collect($this->generalOptions)->filter(function($value, $key) use($optionKey) {
            return $key == $optionKey || in_array($optionKey, $value['options']);
        })
        ->keys()
        ->first();
    }

    protected function optionAsString($key, $value)
    {
        if(is_array($value) && count($value)) {
            $cmd = [];
            foreach($value as $k => $v) {
                $cmd[] = sprintf("-%s %s=%s", $key, $k, $v);
            }

            return implode(' ', $cmd);
        }

        return sprintf("-%s %s", $key, $value);
    }
}
