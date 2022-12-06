<?php

namespace Sunnysideup\UnderConstruction\Tasks;

class GoOnline extends GoOffline
{
    protected $title = 'Go Online / End Under Construction Period';

    private static $segment = 'go-online-or-finish-construction';

    /**
     * @param \SilverStripe\Control\HTTPRequest $request
     * @throws \ReflectionException
     */
    public function run($request)
    {
        $path = $this->getHtAccessPath();
        $currentContent = file_get_contents($path);
        $contentToRemove = $this->getHtAccessContent();
        if (strpos($currentContent, $contentToRemove) !== false) {
            $currentContent = str_replace($contentToRemove, '', $currentContent);
            $currentContent = str_replace($contentToRemove, '', $currentContent);
            $currentContent = str_replace($contentToRemove, '', $currentContent);
        }
        file_put_contents($path, $currentContent);

        return 'Your site is now offline.';
    }
}
