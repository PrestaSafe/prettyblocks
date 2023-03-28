<?php

namespace PrestaSafe\PrettyBlocks\Handler;

use PrestaSafe\PrettyBlocks\Checker\ToolbarAdminChecker;
use PrestaSafe\PrettyBlocks\Checker\ToolbarParameterChecker;

final class ToolbarCheckerHandler
{
    public function __invoke()
    {
        // Maybe other checker in the future ?
        return ToolbarAdminChecker::check() || ToolbarParameterChecker::check();
    }
}
