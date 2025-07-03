<?php
/**
 * Copyright (c) Since 2020 PrestaSafe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@prestasafe.com so we can send you a copy immediately.
 *
 * @author    PrestaSafe <contact@prestasafe.com>
 * @copyright Since 2020 PrestaSafe
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaSafe
 */

namespace PrestaSafe\PrettyBlocks\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use ZipArchive;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
if (!defined('_PS_VERSION_')) { exit; }
class ReleaseCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('prettyblocks:release')
            ->setDescription('Create a release package of the PrettyBlocks module')
            ->addOption(
                'output-dir',
                'o',
                InputOption::VALUE_OPTIONAL,
                'Output directory for the release package',
                _PS_ROOT_DIR_ . '/var/releases'
            )
            ->addOption(
                'module-version',
                null,
                InputOption::VALUE_OPTIONAL,
                'Override version number to append to filename (if not provided, will use module version)',
                null
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('PrettyBlocks Module Release Creator');
        
        $moduleDir = _PS_ROOT_DIR_ . '/modules/prettyblocks';
        $outputDir = $input->getOption('output-dir');
        $versionOverride = $input->getOption('module-version');
        
        // Get module version
        $moduleVersion = $this->getModuleVersion($moduleDir);
        $version = $versionOverride ?: $moduleVersion;
        
        $io->section('Module Information');
        $io->text("Module directory: $moduleDir");
        $io->text("Module version: $moduleVersion");
        if ($versionOverride) {
            $io->text("Version override: $versionOverride");
        }
        
        // Create temporary directory
        $tempDir =_PS_ROOT_DIR_ . '/prettyblocks_release_' . uniqid();
        if(is_dir($tempDir)){
            $this->cleanupTempDir($tempDir, $io);
        }
        $io->section('Creating temporary directory...');
        $io->text("Temporary directory: $tempDir");
        
        if (!mkdir($tempDir, 0755, true)) {
            $io->error("Cannot create temporary directory: $tempDir");
            return 1;
        }
        
        // Copy module to temporary directory
        $io->text('Copying module to temporary directory...');
        if (!$this->copyDirectory($moduleDir, $tempDir, $io)) {
            $io->error("Failed to copy module to temporary directory");
            $this->cleanupTempDir($tempDir, $io);
            return 1;
        }
        
        // Clean uploads folder in temporary directory (not the original)
        $this->cleanUploadsFolder($tempDir, $io);
        
        // Install production dependencies
        $this->installDependencies($tempDir, $io);
        
        // Create output directory if it doesn't exist
        if (!is_dir($outputDir)) {
            if (!mkdir($outputDir, 0755, true)) {
                $io->error("Cannot create output directory: $outputDir");
                $this->cleanupTempDir($tempDir, $io);
                return 1;
            }
        }
        
        // Generate filename
        $filename = 'prettyblocks';
        if ($version) {
            $filename .= '-' . $version;
        } else {
            $filename .= '-' . date('Y-m-d-H-i-s');
        }
        $zipPath = $outputDir . '/' . $filename . '.zip';
        
        if(file_exists($zipPath)){
            unlink($zipPath);
        }
        $io->section('Creating release package...');
        $io->text("Output file: $zipPath");
        
        // Files and directories to exclude
        $excludePatterns = [
            '.git',
            '.gitignore',
            '.DS_Store',
            'node_modules',
            '.npm',
            '.node_repl_history',
            'npm-debug.log*',
            'yarn-debug.log*',
            'yarn-error.log*',
            '.env',
            '.env.local',
            '.env.development.local',
            '.env.test.local',
            '.env.production.local',
            'Thumbs.db',
            '.vscode',
            '.idea',
            '*.tmp',
            '*.temp',
            '*.log',
            '.sass-cache',
            '.cache',
            'coverage',
            '*.tgz',
            '*.tar.gz',
            'config/config.php',
            'config*.xml',
            '_dev/node_modules',
            '_dev/node_modules/*'
        ];
        
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            $io->error("Cannot create zip file: $zipPath");
            $this->cleanupTempDir($tempDir, $io);
            return 1;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tempDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        $filesAdded = 0;
        $filesSkipped = 0;
        
        foreach ($iterator as $file) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($tempDir) + 1);
            
            // Check if file should be excluded
            $shouldExclude = false;
            foreach ($excludePatterns as $pattern) {
                if (strpos($relativePath, $pattern) !== false || 
                    fnmatch($pattern, basename($relativePath))) {
                    $shouldExclude = true;
                    break;
                }
            }
            
            if ($shouldExclude) {
                $filesSkipped++;
                $io->text("Skipping: $relativePath", OutputInterface::VERBOSITY_VERBOSE);
                continue;
            }
            
            // Add file to zip with mailclip/ prefix
            $zipPath_internal = 'prettyblocks/' . $relativePath;
            
            if ($file->isDir()) {
                $zip->addEmptyDir($zipPath_internal);
            } else {
                $this->cleanPhpFiles($filePath);
                $zip->addFile($filePath, $zipPath_internal);
                $filesAdded++;
            }
            
            $io->text("Added: $relativePath", OutputInterface::VERBOSITY_VERBOSE);
        }
        
        $zip->close();
        
        // Cleanup temporary directory
        $this->cleanupTempDir($tempDir, $io);
        
        $io->success([
            'Release package created successfully!',
            "Module version: $moduleVersion",
            "Release version: $version",
            "Files added: $filesAdded",
            "Files skipped: $filesSkipped",
            "Package: $zipPath"
        ]);
        
        // Display file size
        $fileSize = filesize($zipPath);
        $fileSizeFormatted = $this->formatBytes($fileSize);
        $io->text("Package size: $fileSizeFormatted");
        
        return 0;
    }

    private function installDependencies($tempDir, SymfonyStyle $io)
    {
        $io->section('Installing production dependencies...');
        
        $vendorDir = $tempDir . '/vendor';
        
        // Remove existing vendor directory if it exists
        if (is_dir($vendorDir)) {
            $io->text('Removing existing vendor directory...');
            $this->removeDirectory($vendorDir);
        }
        
        // Check if composer.json exists
        $composerJson = $tempDir . '/composer.json';
        if (!file_exists($composerJson)) {
            $io->warning('composer.json not found, skipping dependency installation');
            return;
        }
        
        $io->text('Running composer install --no-dev...');
        
        // Change to temp directory and run composer install
        $command = sprintf(
            'cd %s && composer install --no-dev --optimize-autoloader --no-interaction 2>&1',
            escapeshellarg($tempDir)
        );
        
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            $io->success('Dependencies installed successfully');
            $io->text('Composer output:', OutputInterface::VERBOSITY_VERBOSE);
            foreach ($output as $line) {
                $io->text($line, OutputInterface::VERBOSITY_VERBOSE);
            }
        } else {
            $io->error([
                'Failed to install dependencies',
                'Return code: ' . $returnCode,
                'Output:'
            ]);
            foreach ($output as $line) {
                $io->text($line);
            }
        }
    }

    private function cleanPhpFiles($filePath)
    {
        // Only process PHP files
        if (pathinfo($filePath, PATHINFO_EXTENSION) !== 'php') {
            return;
        }
        
        $content = file_get_contents($filePath);
        
        // Remove empty lines between <?php and /** comment block
        // This helps with PrestaShop Addons validation
        $content = preg_replace('/<\?php\s*\n+(\s*\/\*\*)/', '<?php' . "\n" . '$1', $content);
        
        file_put_contents($filePath, $content);
        $this->addDocFile($filePath);
    }

    private function addDocFile($filePath)
    {
        // Check if file exists and read its content
        $existingContent = '';
        if (file_exists($filePath)) {
            $existingContent = file_get_contents($filePath);
        }
        
        // Get the first few lines to check for <?php and /**
        $lines = explode("\n", $existingContent);
        $firstFewLines = implode("\n", array_slice($lines, 0, 5)); // Check first 5 lines
        
        // Check if the file already contains <?php and /** in the first lines
        $hasPhpTag = strpos($firstFewLines, '<?php') !== false;
        $hasDocComment = strpos($firstFewLines, '/**') !== false;
        
        // Only add documentation if both <?php and /** are missing
        if (!$hasPhpTag || !$hasDocComment) {
            $doc = "<?php
/**
 * Copyright (c) Since 2020 PrestaSafe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@prestasafe.com so we can send you a copy immediately.
 *
 * @author    PrestaSafe <contact@prestasafe.com>
 * @copyright Since 2020 PrestaSafe
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaSafe
 */

";
            
            // If file exists but doesn't have proper header, prepend documentation
            if (!empty($existingContent)) {
                // Remove existing <?php tag if present to avoid duplication
                $existingContent = preg_replace('/^\s*<\?php\s*/', '', $existingContent);
                $doc .= $existingContent;
            }
            
            file_put_contents($filePath, $doc);
        }
    }
    
    /**
     * Get module version from prettyblocks.php file
     */
    private function getModuleVersion($moduleDir)
    {
        $moduleFile = $moduleDir . '/prettyblocks.php';
        
        if (!file_exists($moduleFile)) {
            return null;
        }
        
        $content = file_get_contents($moduleFile);
        
        // Look for $this->version = 'x.x.x';
        if (preg_match('/\$this->version\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * Copy directory recursively
     */
    private function copyDirectory($source, $destination, SymfonyStyle $io)
    {
        if (!is_dir($source)) {
            return false;
        }
        
        if (!is_dir($destination) && !mkdir($destination, 0755, true)) {
            return false;
        }
        
        // Files and directories to exclude during copy
        $excludePatterns = [
            '.git',
            '.gitignore',
            '.DS_Store',
            'node_modules',
            '.npm',
            '.node_repl_history',
            'npm-debug.log*',
            'yarn-debug.log*',
            'yarn-error.log*',
            '.env',
            '.env.local',
            '.env.development.local',
            '.env.test.local',
            '.env.production.local',
            'Thumbs.db',
            '.vscode',
            '.idea',
            '*.tmp',
            '*.temp',
            '*.log',
            '.sass-cache',
            '.cache',
            'coverage',
            '*.tgz',
            '*.tar.gz',
            'config/config.php',
            'config*.xml',
            '_dev/node_modules',
        ];
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            $sourcePath = $file->getRealPath();
            
            // Skip the source directory itself
            if ($sourcePath === $source) {
                continue;
            }
            
            $relativePath = substr($sourcePath, strlen($source) + 1);
            
            // Check if file should be excluded during copy
            $shouldExclude = false;
            foreach ($excludePatterns as $pattern) {
                if (strpos($relativePath, $pattern) !== false || 
                    fnmatch($pattern, basename($relativePath)) ||
                    fnmatch($pattern, $relativePath)) {
                    $shouldExclude = true;
                    break;
                }
            }
            
            if ($shouldExclude) {
                $io->text("Skipping during copy: $relativePath", OutputInterface::VERBOSITY_VERBOSE);
                continue;
            }
            
            $destPath = $destination . '/' . $relativePath;
            
            if ($file->isDir()) {
                if (!is_dir($destPath) && !mkdir($destPath, 0755, true)) {
                    return false;
                }
            } else {
                // Check if source file actually exists and is readable
                if (!file_exists($sourcePath) || !is_readable($sourcePath)) {
                    $io->text("Skipping unreadable file: $relativePath", OutputInterface::VERBOSITY_VERBOSE);
                    continue;
                }
                
                if (!copy($sourcePath, $destPath)) {
                    $io->text("Failed to copy: $relativePath", OutputInterface::VERBOSITY_VERBOSE);
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Clean uploads folder in temporary directory before creating release
     */
    private function cleanUploadsFolder($tempDir, SymfonyStyle $io)
    {
        $uploadsDir = $tempDir . '/uploads';
        
        if (!is_dir($uploadsDir)) {
            $io->text('Uploads folder does not exist in temporary directory, skipping cleanup.');
            return;
        }
        
        $io->section('Cleaning uploads folder in temporary directory...');
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($uploadsDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        $deletedFiles = 0;
        $deletedDirs = 0;
        
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                if (rmdir($file->getRealPath())) {
                    $deletedDirs++;
                    $io->text("Deleted directory: " . $file->getFilename(), OutputInterface::VERBOSITY_VERBOSE);
                }
            } else {
                // Keep index.php file
                if ($file->getFilename() === 'index.php') {
                    $io->text("Keeping: " . $file->getFilename(), OutputInterface::VERBOSITY_VERBOSE);
                    continue;
                }
                
                if (unlink($file->getRealPath())) {
                    $deletedFiles++;
                    $io->text("Deleted file: " . $file->getFilename(), OutputInterface::VERBOSITY_VERBOSE);
                }
            }
        }
        
        $io->text("Uploads folder cleaned in temporary directory: $deletedFiles files and $deletedDirs directories removed (index.php preserved).");
    }
    
    /**
     * Clean up temporary directory
     */
    private function cleanupTempDir($tempDir, SymfonyStyle $io)
    {
        if (!is_dir($tempDir)) {
            return;
        }
        
        $io->text('Cleaning up temporary directory...');
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tempDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        
        rmdir($tempDir);
        $io->text('Temporary directory cleaned up.');
    }
    
    private function formatBytes($size, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }

    /**
     * Remove directory recursively
     */
    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        
        rmdir($dir);
    }
} 