<?php

namespace PrestaSafe\PrettyBlocks\Handler;

use PrestaSafe\PrettyBlocks\Checker\AdminChecker;
use PrestaSafe\PrettyBlocks\Checker\ParameterChecker;

final class ToolbarCheckerHandler
{
    public function __invoke()
    {
        // Maybe other checker in the future ?
        return AdminChecker::check() || ParameterChecker::check();
    }
}
