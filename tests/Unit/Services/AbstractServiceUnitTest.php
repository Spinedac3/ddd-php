<?php

namespace Spineda\DddFoundation\Tests\Unit\Services;

use Spineda\DddFoundation\Contracts\IsService;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;

/**
 * Abstract class for testing services
 *
 * @package Spineda\DddFoundation\Tests
 */
abstract class AbstractServiceUnitTest extends AbstractUnitTest
{
    /**
     * @var  IsService
     */
    protected IsService $service;
}
