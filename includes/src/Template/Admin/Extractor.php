<?php

declare(strict_types=1);

namespace JTL\Template\Admin;

use InvalidArgumentException;
use JTL\Filesystem\Filesystem;
use JTL\Filesystem\LocalFilesystem;
use JTL\Plugin\Admin\Installation\InstallationResponse;
use JTL\Shop;
use League\Flysystem\MountManager;
use Throwable;
use ZipArchive;

/**
 * Class Extractor
 * @package JTL\Template\Admin
 */
class Extractor
{
    private const UNZIP_DIR = \PFAD_ROOT . \PFAD_DBES_TMP;

    private const GIT_REGEX = '/(.*)((-master)|(-[a-zA-Z\d]{40}))\/(.*)/';

    private InstallationResponse $response;

    private MountManager $manager;

    public function __construct()
    {
        $this->response = new InstallationResponse();
        $this->manager  = new MountManager([
            'root' => Shop::Container()->get(LocalFilesystem::class),
            'tpl'  => Shop::Container()->get(Filesystem::class)
        ]);
    }

    public function extractTemplate(string $zipFile): InstallationResponse
    {
        $this->unzip($zipFile);

        return $this->response;
    }

    public function handlExtractionErrors(int $errno, string $errstr): bool
    {
        $this->response->setStatus(InstallationResponse::STATUS_FAILED);
        $this->response->setError($errstr);

        return true;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function moveToTargetDir(string $dirName): bool
    {
        $info = self::UNZIP_DIR . $dirName . \TEMPLATE_XML;
        if (!\file_exists($info)) {
            throw new InvalidArgumentException(
                \sprintf(\__('pluginInstallTemplateDoesNotExist'), \TEMPLATE_XML, $info)
            );
        }
        $ok   = true;
        $base = \PFAD_TEMPLATES;
        try {
            $this->manager->createDirectory('tpl://' . $base . $dirName);
        } catch (Throwable $e) {
            $this->handlExtractionErrors(0, \__('errorDirCreate') . $base . $dirName . ' - ' . $e->getMessage());

            return false;
        }
        foreach ($this->manager->listContents('root://' . \PFAD_DBES_TMP . $dirName, true) as $item) {
            $source = $item->path();
            $target = $base . \str_replace(\PFAD_DBES_TMP, '', \str_replace('root://', '', $source));
            if ($item->isDir()) {
                try {
                    $this->manager->createDirectory('tpl://' . $target);
                } catch (Throwable) {
                    $ok = false;
                }
            } else {
                try {
                    $this->manager->move($source, 'tpl://' . $target);
                } catch (Throwable) {
                    $ok = false;
                    $this->manager->delete('tpl://' . $target);
                    $this->manager->move($source, 'tpl://' . $target);
                }
            }
        }
        $this->manager->deleteDirectory('root://' . \PFAD_DBES_TMP . $dirName);
        if ($ok === true) {
            $this->response->setPath($base . $dirName);

            return true;
        }
        $this->handlExtractionErrors(0, \sprintf(\__('pluginInstallCannotMoveTo'), $base . $dirName));

        return false;
    }

    private function unzip(string $zipFile): bool
    {
        $dirName = '';
        $zip     = new ZipArchive();
        if (!$zip->open($zipFile) || $zip->numFiles === 0) {
            $this->handlExtractionErrors(0, \__('pluginInstallCannotOpenArchive'));

            return false;
        }
        for ($i = 0; $i < $zip->numFiles; $i++) {
            if ($i === 0) {
                $dirName = $zip->getNameIndex($i) ?: '.';
                if (\str_contains($dirName, '.')) {
                    $this->handlExtractionErrors(0, \__('pluginInstallInvalidArchive'));

                    return false;
                }
                \preg_match(self::GIT_REGEX, $dirName, $hits);
                if (\count($hits) >= 3) {
                    $dirName = \str_replace($hits[2], '', $dirName);
                }
                $this->response->setDirName($dirName);
            }
            $filename = $zip->getNameIndex($i);
            if ($filename === false) {
                continue;
            }
            \preg_match(self::GIT_REGEX, $filename, $hits);
            if (\count($hits) >= 3) {
                $zip->renameIndex($i, \str_replace($hits[2], '', $filename));
                $filename = $zip->getNameIndex($i);
                if ($filename === false) {
                    continue;
                }
            }
            if ($zip->extractTo(self::UNZIP_DIR, $filename)) {
                $this->response->addFileUnpacked($filename);
            } else {
                $this->response->addFileFailed($filename);
            }
        }
        $zip->close();
        $this->response->setPath(self::UNZIP_DIR . $dirName);
        try {
            $this->moveToTargetDir($dirName);
        } catch (InvalidArgumentException $e) {
            $this->response->setStatus(InstallationResponse::STATUS_FAILED);
            $this->response->addMessage($e->getMessage());

            return false;
        }

        return true;
    }
}
