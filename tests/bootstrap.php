<?php

ini_set('memory_limit', -1);

use Spineda\DddFoundation\Builders\Services\System\MainConfigurationServiceBuilder;

require __DIR__ . '/../vendor/autoload.php';

// Builds the main configuration service, letting it fail if the file does not exist
$configurationFile = __DIR__ . '/configuration.yaml';
putenv('TESTS_CONFIGURATION_FILE=' . $configurationFile);
MainConfigurationServiceBuilder::buildFromFile($configurationFile);
