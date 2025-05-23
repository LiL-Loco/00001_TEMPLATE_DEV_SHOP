<?php

declare(strict_types=1);

namespace JTL\Console\Command\Cache;

use JTL\Console\Command\Command;
use JTL\Filesystem\Filesystem;
use JTL\Filesystem\LocalFilesystem;
use JTL\Shop;
use League\Flysystem\FileAttributes;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Class DeleteTemplateCacheCommand
 * @package JTL\Console\Command\Cache
 */
class DeleteTemplateCacheCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this->setName('cache:tpl:delete')
            ->setDescription('Delete template cache')
            ->addOption('admin', 'a', InputOption::VALUE_NONE, 'Also delete admin template cache');
    }

    private function deleteAdminTplCache(Filesystem $filesystem): void
    {
        foreach ($filesystem->listContents(\PFAD_ADMIN . \PFAD_COMPILEDIR) as $item) {
            /** @var FileAttributes $item */
            if ($item->isDir()) {
                try {
                    $filesystem->deleteDirectory($item->path());
                } catch (Throwable) {
                }
            } else {
                try {
                    $filesystem->delete($item->path());
                } catch (Throwable) {
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io             = $this->getIO();
        $adminTpl       = $this->getOption('admin');
        $filesystem     = Shop::Container()->get(LocalFilesystem::class);
        $activeTemplate = Shop::Container()->getTemplateService()->getActiveTemplate(false);
        if ($adminTpl) {
            $this->deleteAdminTplCache($filesystem);
        }
        try {
            $filesystem->deleteDirectory('/templates_c/' . $activeTemplate->getDir());
            $io->success('Template cache deleted.');

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $io->warning('Could not delete: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
