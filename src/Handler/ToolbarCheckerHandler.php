<?php

namespace PrestaSafe\PrettyBlocks\Handler;

use PrestaSafe\PrettyBlocks\Checker\Toolbar\ToolbarParameterChecker;

final class ToolbarCheckerHandler
{
    public static function canDisplay()
    {
        // Maybe other checker in the future ?
        return ToolbarParameterChecker::check();
    }
}
