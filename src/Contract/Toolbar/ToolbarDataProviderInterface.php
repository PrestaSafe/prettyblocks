<?php

namespace PrestaSafe\PrettyBlocks\Contract\Toolbar;

interface ToolbarDataProviderInterface
{
    /**
     * @param string $term
     * @return array
     */
	public static function getByTerms(string $term): array;
	static function formatData(array $data): array;
}
