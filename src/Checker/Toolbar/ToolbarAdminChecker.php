<?php

namespace PrestaSafe\PrettyBlocks\Checker\Toolbar;

use Cookie;
use Exception;
use PrestaSafe\PrettyBlocks\Contract\Toolbar\ToolbarCheckerInterface;
use PrestaShopLogger;

final class ToolbarAdminChecker implements ToolbarCheckerInterface
{
	/**
	 * @return bool
	 */
	public static function check(): bool
	{
		try {
			return (bool)(new Cookie('psAdmin'))->id_employee;
		} catch (Exception $e) {
			PrestaShopLogger::addLog($e->getMessage());
			return false;
		}
	}
}
