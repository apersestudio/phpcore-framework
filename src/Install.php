<?php

namespace PC;

use PC\Core;
use PC\Singletons\Files;
use Composer\Installer\PackageEvent;

class Install {

    public static function postInstallCMD($event) {
        echo "You are here";
    }

    public static function PostPackageInstall(PackageEvent $event) {

        $installedPackage = $event->getOperation()->getPackage();
        echo "[YOU ARE HERE]";
        echo "[".$installedPackage."]";

        // Copy files and directories to the root folder
        // only if those did not exists
        $root = Core::DIR_ROOT();
        $vendor = Core::DIR_VENDOR();
        
        echo "[".$root."]";
        echo "[".$vendor."]";
        
        $frameworkInfo = json_decode(file_get_contents($root."/composer.json"));
        $frameworkPath = $vendor."/".$frameworkInfo->name;

        $frameworkInfo->name = "Hello world";
        echo json_encode($frameworkInfo);

        $directoriesToCopy = ["app","public"];
        foreach ($directoriesToCopy as $dir) {
            $vendorPath = $frameworkPath."/".$dir;
            $rootPath = $root."/".$dir;
            if (is_dir($vendorPath) === true && is_dir($rootPath) === false) {
                Files::copyDirectory($vendorPath, $root);
            }
        }

        $filesToCopy = [".env", ".gitignore", "LICENSE", "phpcore"];
        foreach ($filesToCopy as $file) {
            $vendorPath = $frameworkPath."/".$file;
            $rootPath = $root."/".$file;
            if (is_file($vendorPath) === true && is_file($rootPath) === false) {
                Files::copy($vendorPath, $root);
            }
        }
    }

}