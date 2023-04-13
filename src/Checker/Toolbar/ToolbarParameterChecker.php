<?php

namespace PrestaSafe\PrettyBlocks\Checker\Toolbar;

use PrestaSafe\PrettyBlocks\Contract\Toolbar\ToolbarCheckerInterface;
use Tools;

final class ToolbarParameterChecker implements ToolbarCheckerInterface
{
	/**
	 * @return bool
	 */
	public static function check(): bool
	{
		return Tools::getIsset('prettyblock_preview');
	}
}
