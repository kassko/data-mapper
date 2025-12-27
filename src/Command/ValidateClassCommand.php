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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command to validate metadata for a specific class
 */
class ValidateClassCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('datamapper:validate:class')
            ->setDescription('Validate DataMapper metadata for a specific class')
            ->setHelp('This command validates that a class uses DataMapper attributes correctly.')
            ->addArgument('class', InputArgument::REQUIRED, 'Fully qualified class name to validate');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $className = $input->getArgument('class');

        $io->title('DataMapper Metadata Validation');
        $io->text("Validating class: <info>{$className}</info>");
        $io->newLine();

        $validator = new MetadataValidator();
        $result = $validator->validateClass($className);

        // Display results
        if ($result->isValid()) {
            $io->success("Class {$className} is valid!");
            
            if ($result->hasWarnings()) {
                $io->warning('Warnings found:');
                foreach ($result->warnings as $warning) {
                    $io->text("  • {$warning}");
                }
            }
            
            return Command::SUCCESS;
        } else {
            $io->error("Class {$className} has validation errors!");
            
            foreach ($result->errors as $error) {
                $io->text("<error>  ✗</error> {$error}");
            }
            
            if ($result->hasWarnings()) {
                $io->newLine();
                $io->warning('Warnings:');
                foreach ($result->warnings as $warning) {
                    $io->text("  ⚠ {$warning}");
                }
            }
            
            return Command::FAILURE;
        }
    }
}
