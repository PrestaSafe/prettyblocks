<?php

namespace PrestaSafe\PrettyBlocks\Checker;

use PrestaSafe\PrettyBlocks\Contract\CheckerInterface;


final class ParameterChecker implements CheckerInterface
{
	/**
	 * @return bool
	 */
	public static function check(): bool
	{
		return \Tools::getIsset('prettyblock_preview');
	}
}
