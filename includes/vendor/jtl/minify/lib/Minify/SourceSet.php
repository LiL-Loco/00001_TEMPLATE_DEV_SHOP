<?php

declare(strict_types=1);

/**
 * Class Minify_SourceSet
 * @package Minify
 */

/**
 * @package Minify
 */
class Minify_SourceSet
{
    /**
     * Get unique string for a set of sources
     *
     * @param Minify_SourceInterface[] $sources Minify_Source instances
     *
     * @return string
     */
    public static function getDigest($sources)
    {
        $info = [];
        foreach ($sources as $source) {
            $info[] = [
                $source->getId(),
                $source->getMinifier(),
                $source->getMinifierOptions()
            ];
        }

        return md5(serialize($info));
    }
}
