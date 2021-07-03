<?php

namespace Permafrost\RayScan\Configuration;

use Symfony\Component\Console\Input\InputInterface;

class ConfigurationFactory
{
    public static function create(InputInterface $input): Configuration
    {
        $path = $input->getArgument('path');

        $hideProgress = $input->hasOption('no-progress') && $input->getOption('no-progress') === true;
        $hideSnippets = $input->hasOption('no-snippets') && $input->getOption('no-snippets') === true;

        return new Configuration($path, $hideSnippets, $hideProgress);
    }
}
