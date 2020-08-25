<?php

declare(strict_types=1);


namespace App\Component\Exporter\Strategy;


use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractExportStrategy implements ExportStrategyInterface
{
    function createFile(Filesystem $filesystem, string $dataDir, string $datetime, string $filename):void
    {
        $filesystem->mkdir($dataDir);
        $filesystem->mkdir($dataDir.$datetime.'/');
        $filesystem->touch($dataDir.$datetime.'/'.$filename);
    }
}