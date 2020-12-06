<?php

use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Dev\BuildTask;

use SilverStripe\Control\Director;
use SilverStripe\Control\Controller;


class GoOffline extends BuildTask
{

    private static $segment = 'go-offline-or-under-construction';

    protected $title = 'Go Offline / Start Under Construction';

    protected $description = 'Careful - makes the site inaccessible for everyone except you.  Risky action!';

    /**
     * @param \SilverStripe\Control\HTTPRequest $request
     * @throws \ReflectionException
     */
    public function run($request)
    {
        $path = $this->getHtAccessPath();
        $currentContent = file_get_contents($path);
        $contentToAdd = $this->getHtAccessContent();
        if(strpos($currentContent, $contentToAdd) === false)  {
            $currentContent = $contentToAdd . $currentContent;
        }
        file_put_contents($path, $currentContent);

        return 'Your site is now offline.';
    }

    protected function getHtAccessPath() : string
    {
        return Controller::join_links(Director::baseFolder(), Director::publicDir(), '.htaccess');

    }
    protected function getHtAccessContent() : string
    {
        $siteConfig = SiteConfig::current_site_config();
        return $siteConfig->getUnderConstructionCalculatedValues()->getHtAccessContent();

    }

}
