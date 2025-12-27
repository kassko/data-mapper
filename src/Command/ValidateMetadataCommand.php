<?php

declare(strict_types=1);

/*
 * This file is part of DataMapper.
 *
 * Copyright 2025 kassko 
 *
 * For the full copyright and license information,
 * please view the LICENSE and NOTICE files that were distributed with this source code.
 */

namespace Kassko\DataMapper\Command;

use Kassko\DataMapper\Validation\MetadataValidator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

/**
 * Console command to validate metadata for all classes in a directory/namespace
 */
class ValidateMetadataCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('datamapper:validate')
            ->setDescription('Validate DataMapper metadata for all classes in a directory or namespace')
            ->setHelp('This command validates that all classes in a directory use DataMapper attributes correctly.')
            ->addArgument('path', InputArgument::REQUIRED, 'Path to directory containing data object classes')
            ->addOption('namespace', null, InputOption::VALUE_REQUIRED, 'Base namespace for the classes')
            ->addOption('fail-on-warning', null, InputOption::VALUE_NONE, 'Fail if warnings are found');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getArgument('path');
        $namespace = $input->getOption('namespace');
        $failOnWarning = $input->getOption('fail-on-warning');

        $io->title('DataMapper Metadata Validation');
        
        if (!is_dir($path)) {
            $io->error("Directory not found: {$path}");
            return Command::FAILURE;
        }

        // Find all PHP files in directory
        $finder = new Finder();
        $finder->files()->in($path)->name('*.php');

        $classes = [];
        foreach ($finder as $file) {
            $className = $this->getClassNameFromFile($file->getPathname(), $namespace);
            if ($className && class_exists($className)) {
                $classes[] = $className;
            }
        }

        if (empty($classes)) {
            $io->warning("No classes found in {$path}");
            return Command::SUCCESS;
        }

        $io->text("Found " . count($classes) . " classe(s) to validate");
        $io->newLine();

        $validator = new MetadataValidator();
        $results = $validator->validateClasses($classes);

        // Display results
        $validCount = 0;
        $invalidCount = 0;
        $warningCount = 0;

        foreach ($results as $result) {
            if ($result->isValid()) {
                $validCount++;
                if ($result->hasWarnings()) {
                    $warningCount++;
                }
            } else {
                $invalidCount++;
            }

            // Show detailed output
            if (!$result->isValid() || $result->hasWarnings()) {
                $io->section($result->className);
                
                if (!$result->isValid()) {
                    foreach ($result->errors as $error) {
                        $io->text("<error>  ✗</error> {$error}");
                    }
                }
                
                if ($result->hasWarnings()) {
                    foreach ($result->warnings as $warning) {
                        $io->text("<comment>  ⚠</comment> {$warning}");
                    }
                }
                
                $io->newLine();
            }
        }

        // Summary
        $io->section('Summary');
        $io->text("Total classes: " . count($classes));
        $io->text("<info>Valid: {$validCount}</info>");
        if ($invalidCount > 0) {
            $io->text("<error>Invalid: {$invalidCount}</error>");
        }
        if ($warningCount > 0) {
            $io->text("<comment>With warnings: {$warningCount}</comment>");
        }

        // Determine exit code
        if ($invalidCount > 0) {
            return Command::FAILURE;
        }
        
        if ($failOnWarning && $warningCount > 0) {
            $io->warning('Warnings found and --fail-on-warning is set');
            return Command::FAILURE;
        }

        $io->success('All classes are valid!');
        return Command::SUCCESS;
    }

    /**
     * Extract class name from PHP file
     */
    private function getClassNameFromFile(string $filePath, ?string $baseNamespace): ?string
    {
        $content = file_get_contents($filePath);
        
        // Try to extract namespace and class name
        if (preg_match('/namespace\s+([^;]+);/i', $content, $namespaceMatch) &&
            preg_match('/class\s+(\w+)/i', $content, $classMatch)) {
            return $namespaceMatch[1] . '\\' . $classMatch[1];
        }
        
        // Fallback to base namespace if provided
        if ($baseNamespace !== null && preg_match('/class\s+(\w+)/i', $content, $classMatch)) {
            return $baseNamespace . '\\' . $classMatch[1];
        }
        
        return null;
    }
}
