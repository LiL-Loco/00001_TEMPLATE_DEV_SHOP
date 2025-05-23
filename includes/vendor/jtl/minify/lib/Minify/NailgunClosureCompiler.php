<?php

declare(strict_types=1);

/**
 * Class Minify_ClosureCompiler
 * @package Minify
 */

/**
 * Run Closure Compiler via NailGun
 *
 * @package Minify
 * @author Elan Ruusamäe <glen@delfi.ee>
 * @link https://github.com/martylamb/nailgun
 */
class Minify_NailgunClosureCompiler extends Minify_ClosureCompiler
{
    public const NG_SERVER = 'com.martiansoftware.nailgun.NGServer';
    public const CC_MAIN   = 'com.google.javascript.jscomp.CommandLineRunner';

    /**
     * For some reasons Nailgun thinks that it's server
     * broke the connection and returns 227 instead of 0
     * We'll just handle this here instead of fixing
     * the nailgun client itself.
     *
     * It also sometimes breaks on 229 on the devbox.
     * To complete this whole madness and made future
     * 'fixes' easier I added this nice little array...
     * @var array
     */
    private static $NG_EXIT_CODES = [0, 227, 229];

    /**
     * Filepath of "ng" executable (from Nailgun package)
     *
     * @var string
     */
    public static $ngExecutable = 'ng';

    /**
     * Filepath of the Nailgun jar file.
     *
     * @var string
     */
    public static $ngJarFile;

    /**
     * Get command to launch NailGun server.
     *
     * @return array
     */
    protected function getServerCommandLine()
    {
        $this->checkJar(self::$ngJarFile);
        $this->checkJar(self::$jarFile);

        $classPath = [
            self::$ngJarFile,
            self::$jarFile,
        ];

        // The command for the server that should show up in the process list
        return [
            self::$javaExecutable,
            '-server',
            '-cp',
            implode(':', $classPath),
            self::NG_SERVER,
        ];
    }

    /**
     * @return array
     */
    protected function getCompilerCommandLine()
    {
        return [
            self::$ngExecutable,
            escapeshellarg(self::CC_MAIN)
        ];
    }

    /**
     * @param string $tmpFile
     * @param array  $options
     * @return string
     * @throws Minify_ClosureCompiler_Exception
     */
    protected function compile($tmpFile, $options)
    {
        $this->startServer();

        $command = $this->getCommand($options, $tmpFile);

        return implode("\n", $this->shell($command, self::$NG_EXIT_CODES));
    }

    private function startServer()
    {
        $serverCommand = implode(' ', $this->getServerCommandLine());
        $psCommand     = $this->shell('ps -o cmd= -C ' . self::$javaExecutable);
        if (in_array($serverCommand, $psCommand, true)) {
            // already started!
            return;
        }

        $this->shell("$serverCommand </dev/null >/dev/null 2>/dev/null & sleep 10");
    }
}
