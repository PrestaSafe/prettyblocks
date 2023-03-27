<?php

namespace PrestaSafe\PrettyBlocks\Checker;

use Cookie;
use Exception;
use PrestaSafe\PrettyBlocks\Contract\CheckerInterface;
use PrestaShopLogger;

final class AdminChecker implements CheckerInterface
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
