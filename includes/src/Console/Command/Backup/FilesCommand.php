<?php

declare(strict_types=1);

namespace JTL\Console\Command\Backup;

use JTL\Console\Command\Command;
use JTL\Filesystem\Filesystem;
use JTL\Filesystem\LocalFilesystem;
use JTL\Shop;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class FilesCommand
 * @package JTL\Console\Command\Backup
 */
class FilesCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this->setName('backup:files')
            ->setDescription('Backup shop content')
            ->addOption(
                'exclude-dir',
                'x',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Exclude directory'
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Filesystem $fs */
        $fs       = Shop::Container()->get(LocalFilesystem::class);
        $io       = $this->getIO();
        $archive  = \PFAD_ROOT . \PFAD_EXPORT_BACKUP . \date('YmdHis') . '_file_backup.zip';
        $excludes = \array_merge(
            [
                'export',
                'templates_c',
                'build',
                'admin/templates_c',
                'dbeS/tmp',
                'dbeS/logs',
                'jtllogs',
                'install/logs'
            ],
            $this->getOption('exclude-dir')
        );
        $finder   = Finder::create()
            ->ignoreVCS(false)
            ->ignoreDotFiles(false)
            ->exclude($excludes)
            ->in(\PFAD_ROOT);

        $io->progress(
            static function (callable $mycb) use ($fs, $archive, $finder): void {
                $fs->zip($finder, $archive, static function ($count, $index) use (&$mycb) {
                    $mycb($count, $index);
                });
            },
            'Creating archive [%bar%] %percent:3s%%'
        );
        $io->newLine();
        $io->success('Archive "' . $archive . '" created.');

        return Command::SUCCESS;
    }
}
