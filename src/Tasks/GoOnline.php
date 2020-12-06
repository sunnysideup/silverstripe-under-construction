<?php

use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Dev\BuildTask;

use SilverStripe\Control\Director;
use SilverStripe\Control\Controller;


class GoOnline extends GoOffline
{

    private static $segment = 'go-online-or-finish-construction';

    protected $title = 'Go Online / End Under Construction Period';

    /**
     * @param \SilverStripe\Control\HTTPRequest $request
     * @throws \ReflectionException
     */
    public function run($request)
    {
        $path = $this->getHtAccessPath();
        $currentContent = file_get_contents($path);
        $contentToRemove = $this->getHtAccessContent();
        if(strpos($currentContent, $contentToRemove) !== false)  {
            $currentContent = str_replace($contentToRemove, '', $currentContent);
            $currentContent = str_replace($contentToRemove, '', $currentContent);
            $currentContent = str_replace($contentToRemove, '', $currentContent);
        }
        file_put_contents($path, $currentContent);

        return 'Your site is now offline.';
    }

}
