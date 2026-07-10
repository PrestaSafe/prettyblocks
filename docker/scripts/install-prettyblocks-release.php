<?php

declare(strict_types=1);

use PrestaShop\PrestaShop\Core\Module\SourceHandler\ZipSourceHandler;

if ($argc !== 2 || !is_file($argv[1])) {
    fwrite(STDERR, "A readable PrettyBlocks release ZIP is required.\n");
    exit(1);
}

require '/var/www/html/config/config.inc.php';

try {
    $context = Context::getContext();
    $context->employee = new Employee(1);

    $sourceHandler = new ZipSourceHandler(_PS_MODULE_DIR_, $context->getTranslator());
    $moduleName = $sourceHandler->getModuleName($argv[1]);

    if ('prettyblocks' !== $moduleName) {
        throw new RuntimeException(sprintf('Unexpected module name in ZIP: %s', $moduleName));
    }

    $sourceHandler->handle($argv[1]);
    printf("Validated and extracted %s from the release ZIP.\n", $moduleName);
} catch (Throwable $exception) {
    fwrite(STDERR, $exception::class . ': ' . $exception->getMessage() . "\n");
    exit(1);
}
