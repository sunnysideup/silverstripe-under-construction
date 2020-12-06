<?php

namespace Sunnysideup\UnderConstruction\Extensions;

use SilverStripe\Forms\OptionsetField;
use Sunnysideup\UnderConstruction\Api\CalculatedValues;
use Sunnysideup\UnderConstruction\Tasks\GoOffline;
use Sunnysideup\UnderConstruction\Tasks\GoOnline;

use SilverStripe\Control\Controller;
use SilverStripe\Forms\TextField;
use SilverStripe\Control\Director;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\FieldList;
use Page;
use Symbiote\SortableMenu\SortableMenuExtensionException;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBBoolean;

use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\Core\Config\Config;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use SilverStripe\CMS\Model\SiteTree;

use SilverStripe\AssetAdmin\Forms\UploadField;

use SilverStripe\Assets\Image;
use SilverStripe\Assets\File;

use SilverStripe\View\ArrayData;
use Symbiote\Multisites\Model\Site;

class SiteConfigExtension extends DataExtension
{

    private static $db = [
        'UnderConstructionOnOff' => 'Enum("Online,Offline", "Online")',
        'UnderConstructionMinutesOffline' => 'Int',
        'UnderConstructionTitle' => 'Varchar',
        'UnderConstructionSubTitle' => 'Varchar',
        'UnderConstructionExcludedIps' => 'Text',
        'UnderConstructionOutcome' => 'Text',
    ];

    private static $has_one = [
        'UnderConstructionImage' => Image::class,
    ];

    private static $defaults = [
        'UnderConstructionMinutesOffline' => 'Online',
        'UnderConstructionMinutesOffline' => 20,
        'UnderConstructionTitle' => 'Sorry, we are offline for an upgrade.',
        'UnderConstructionSubTitle' => 'Please come back soon.',
    ];

    private static $owns = [
        'UnderConstructionImage',
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $owner = owner;
        $fields->removeByName('UnderConstructionOutcome');
        $fields->addFieldsToTab(
            'Root.Offline',
            [
                OptionsetField::create(
                    'UnderConstructionOnOff',
                    'Is the site Online or Offline',
                    ['Online' => 'Online', 'Offline' => 'Offline']
                )
                    ->setDescription('Make the site go Online / Offline.'),
                ReadonlyField::create(
                    'UnderConstructionOutcome',
                    'Outcome of last Action ...'
                )
                    ->setDescription('Was the last action successful?'),
                NumericField::create(
                    'UnderConstructionMinutesOffline',
                    'Minutes Offline'
                )
                    ->setDescription('Indication to the user for how long the site will be offline.'),
                TextField::create(
                    'UnderConstructionTitle',
                    'Page Title'
                ),
                TextField::create(
                    'UnderConstructionSubTitle',
                    'Page Sub-Title'
                ),
                TextField::create(
                    'UnderConstructionExcludedIps',
                    'Excluded IPs'
                )
                    ->setDescription('Separated by comma. Your IP address ('.Controller::curr()->getRequest()->getIp().') will be added automatically.'),
                UploadField::create(
                    'UnderConstructionImage',
                    'Background Image'
                )
                    ->setFolderName('offline/images')
                    ->setAllowedFileCategories('image')
                    ->setIsMultiUpload(false),
            ]
        );
        $fileName = $owner->getUnderConstructionCalculatedValues()->UnderConstructionFilePath();
        if(file_exists($fileName)) {
            $publicUrl = $this->getUnderConstructionCalculatedValues()->UnderConstructionUrlPath();
            $html = '<a href="'.$publicUrl.'" target="_offline">'.$publicUrl.'</a>';
        } else {
            $html = 'Please complete details above and save to create your offline file.';
        }
        $fields->addFieldsToTab(
            'Root.Offline',
            [
                ReadonlyField::create(
                    'UnderConstructionPublicUrl',
                    'Preview',
                    DBField::create_field(
                        'HTMLText',
                        $html
                    )
                ),
            ]
        );
        return $fields;
    }

    private $underConstructionCalculatedValues = null;

    public function getUnderConstructionCalculatedValues() : CalculatedValues
    {
        if($this->underConstructionCalculatedValues === null) {
            $this->underConstructionCalculatedValues = CalculatedValues::create($this->owner);
        }
        return $this->underConstructionCalculatedValues;
    }

    public function onBeforeWrite()
    {
        $currentIp = Controller::curr()->getRequest()->getIp();
        $array = explode(',', $this->owner->UnderConstructionExcludedIps);
        $array = array_map('trim', $array);
        $array = array_filter($array);
        if ($currentIp) {
            if(! in_array($currentIp, $array)) {
                $array[] = $currentIp;
            }
        }
        $this->owner->UnderConstructionExcludedIps = implode(',', $array);
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        $this->getUnderConstructionCalculatedValues()->CreateFiles();
        if ($this->owner->isChanged('UnderConstructionOnOff')) {
            if($this->owner->UnderConstructionOnOff === 'Offline') {
                $task = Injector::inst()->get(GoOffline::class);

            } else {
                $task = Injector::inst()->get(GoOnline::class);
            }
            $this->owner->UnderConstructionOutcome = $task->run(null);
        }
    }

}
