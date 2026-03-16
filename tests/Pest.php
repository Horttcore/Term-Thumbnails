<?php

declare(strict_types=1);

uses()->beforeEach(function (): void {
    \Brain\Monkey\setUp();
})->afterEach(function (): void {
    \Brain\Monkey\tearDown();
    Mockery::close();
})->in('Unit');
