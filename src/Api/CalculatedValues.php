<?php

namespace Sunnysideup\UnderConstruction\Api;

use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Image;

use SilverStripe\Control\Controller;

use SilverStripe\Control\Director;

use SilverStripe\Core\Config\Config;


use SilverStripe\ORM\ArrayList;
use SilverStripe\SiteConfig\SiteConfig;

use SilverStripe\View\ArrayData;
use SilverStripe\View\ViewableData;


use Sunnysideup\UnderConstruction\Tasks\GoOffline;

use Sunnysideup\UnderConstruction\Tasks\GoOnline;

class CalculatedValues extends ViewableData
{
    private const UNDER_CONSTRUCTION_FOLDER_NAME = 'offline';

    private const UNDER_CONSTRUCTION_FILE_NAME = 'offline.php';

    protected $sc = null;

    public function __construct(SiteConfig $siteConfig)
    {
        parent::__construct();
        $this->sc = $siteConfig;
    }

    public static function go_offline_link(): string
    {
        return '/dev/tasks/' . Config::inst()->get(GoOffline::class, 'segment');
    }

    public static function go_online_link(): string
    {
        return '/dev/tasks/' . Config::inst()->get(GoOnline::class, 'segment');
    }

    public function getSiteConfig()
    {
        return $this->sc;
    }

    public function CreateFiles()
    {
        //create html
        $dir = dirname($this->UnderConstructionFilePath());
        Folder::find_or_make($dir);
        if(! file_exists($dir)) {
            mkdir($dir);
        }
        if (file_exists($dir)) {
            $html = $this->renderWith('Sunnysideup\\UnderConstruction\\UnderConstructionPage');
            $fileName = $this->UnderConstructionFilePath();
            if (file_exists($fileName)) {
                unlink($fileName);
            }
            //delete timestamp
            if (file_exists($fileName . '.txt')) {
                unlink($fileName . '.txt');
            }
            //create image
            file_put_contents($fileName, $html);
            $image = $this->sc->UnderConstructionImage();
            if ($image && $image->exists()) {
                $imageName = $this->UnderConstructionImagePath();
                if (file_exists($imageName)) {
                    unlink($imageName);
                }
                $image->copyFile($imageName);
            }
        } else {
            $this->sc->UnderConstructionOutcome = 'Could not create: ' . $dir;
            $this->sc->write();
        }
    }

    /**
     * arraylist of ips with two values: Ip and IpEscaped
     * @return ArrayList [description]
     */
    public function UnderConstructionIpAddresses(): ArrayList
    {
        $array = explode(',', $this->sc->UnderConstructionExcludedIps);
        $al = ArrayList::create();
        foreach ($array as $ip) {
            $ipEscaped = str_replace('.', '\\.', $ip);
            $al->push(ArrayData::create(['Ip' => $ip, 'IpEscaped' => $ipEscaped]));
        }

        return $al;
    }

    /**
     * something like /var/www/mysite/public/offline/offline.php
     * @return string
     */
    public function UnderConstructionFilePath(): string
    {
        return Controller::join_links(
            Director::baseFolder(),
            Director::publicDir(),
            $this->UnderConstructionFolderName(),
            $this->UnderConstructionFileName()
        );
    }

    /**
     * something like https://mysite.com/offline/offline.php.
     * @return string
     */
    public function UnderConstructionUrlPath(): string
    {
        return Controller::join_links(
            Director::absoluteBaseURL(),
            $this->UnderConstructionFolderName(),
            $this->UnderConstructionFileName()
        );
    }

    /**
     * something like /var/www/mysite/public/offline/offline.php.img
     * @return string
     */
    public function UnderConstructionImagePath(): string
    {
        $extension = $this->sc->UnderConstructionImage()->getExtension();

        return $this->UnderConstructionFilePath() . '.' . $extension;
    }

    /**
     * something like offline
     * @return string
     */
    public function UnderConstructionFolderName(): string
    {
        return self::UNDER_CONSTRUCTION_FOLDER_NAME;
    }

    /**
     * something like offline
     * @return string
     */
    public function UnderConstructionFileName(): string
    {
        return self::UNDER_CONSTRUCTION_FILE_NAME;
    }

    /**
     * something like offline.php.png.
     * @return string
     */
    public function UnderConstructionImageName(): string
    {
        if($this->sc->UnderConstructionImageID) {
            if($this->sc->UnderConstructionImage()->exists()) {
                return $this->sc->UnderConstructionImage()->getFilename();
            }
        }
        return '';
    }

    public function getHtAccessContent(): string
    {
        $txt = $this->renderWith('Sunnysideup\\UnderConstruction\\UnderConstructionHtAccess');

        $array = explode(PHP_EOL, $txt);

        return PHP_EOL . implode(PHP_EOL, $array) . PHP_EOL;
    }
}
