<?php

namespace Sunnysideup\UnderConstruction\Tasks;

use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;

use SilverStripe\Dev\BuildTask;
use SilverStripe\SiteConfig\SiteConfig;

class GoOffline extends BuildTask
{
    protected $title = 'Go Offline / Start Under Construction';

    protected $description = 'Careful - makes the site inaccessible for everyone except you.  Risky action!';

    private static $segment = 'go-offline-or-under-construction';

    /**
     * @param \SilverStripe\Control\HTTPRequest $request
     * @throws \ReflectionException
     */
    public function run($request)
    {
        if ($this->isReady()) {
            $path = $this->getHtAccessPath();
            $currentContent = file_get_contents($path);
            $contentToAdd = $this->getHtAccessContent();
            if (strpos($currentContent, $contentToAdd) === false) {
                $currentContent = $contentToAdd . $currentContent;
            }
            file_put_contents($path, $currentContent);

            return 'Your site is now offline.';
        }
        return 'Your site is not ready to go offline.';
    }

    protected function getHtAccessPath(): string
    {
        return Controller::join_links(Director::baseFolder(), Director::publicDir(), '.htaccess');
    }

    protected function getHtAccessContent(): string
    {
        $siteConfig = SiteConfig::current_site_config();

        return $siteConfig->getUnderConstructionCalculatedValues()->getHtAccessContent();
    }

    protected function isReady(): bool
    {
        $siteConfig = SiteConfig::current_site_config();

        return $siteConfig->getUnderConstructionCalculatedValues()->UnderConstructionIsReady();
    }
}
