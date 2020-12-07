<?php

namespace Sunnysideup\UnderConstruction\Extensions;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Injector\Injector;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\TextField;


use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBField;
use Sunnysideup\UnderConstruction\Api\CalculatedValues;

use Sunnysideup\UnderConstruction\Tasks\GoOffline;

use Sunnysideup\UnderConstruction\Tasks\GoOnline;

class SiteConfigExtension extends DataExtension
{
    protected static $loop_count = 0;

    private static $db = [
        'UnderConstructionOnOff' => 'Enum("Online,Offline", "Online")',
        'UnderConstructionMinutesOffline' => 'Int',
        'UnderConstructionTitle' => 'Varchar',
        'UnderConstructionSubTitle' => 'Varchar',
        'UnderConstructionExcludedIps' => 'Varchar',
        'UnderConstructionOutcome' => 'Text',
    ];

    private static $has_one = [
        'UnderConstructionImage' => Image::class,
    ];

    private static $defaults = [
        'UnderConstructionOnOff' => 'Online',
        'UnderConstructionMinutesOffline' => 20,
        'UnderConstructionTitle' => 'Sorry, we are offline for an upgrade.',
        'UnderConstructionSubTitle' => 'Please come back soon.',
    ];

    private static $owns = [
        'UnderConstructionImage',
    ];

    private $underConstructionCalculatedValues = null;

    public function updateCMSFields(FieldList $fields)
    {
        $owner = $this->owner;
        $fields->removeByName('UnderConstructionOutcome');
        $fields->addFieldsToTab(
            'Root.Offline',
            [
                OptionsetField::create(
                    'UnderConstructionOnOff',
                    'Is the site Online or Offline',
                    ['Online' => 'Online', 'Offline' => 'Offline']
                )
                    ->setDescription('Make the site go Online / Offline. Please use with care!'),
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
                    ->setDescription('Separated by comma. Your IP address (' . Controller::curr()->getRequest()->getIp() . ') will be added automatically.'),
                UploadField::create(
                    'UnderConstructionImage',
                    'Background Image'
                )
                    ->setFolderName('offline/images')
                    ->setAllowedFileCategories('image')
                    ->setIsMultiUpload(false),
            ]
        );
        if($this->getUnderConstructionCalculatedValues()->UnderConstructionIsReady()) {
            $publicUrl = $this->getUnderConstructionCalculatedValues()->UnderConstructionUrlPath();
            $html = '<a href="' . $publicUrl . '" target="_offline">' . $publicUrl . '</a>';
        } else {
            $html = 'No offline page has been created yet.';
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

    public function getUnderConstructionCalculatedValues(): CalculatedValues
    {
        if ($this->underConstructionCalculatedValues === null) {
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
            if (! in_array($currentIp, $array, true)) {
                $array[] = $currentIp;
            }
        }
        $this->owner->UnderConstructionExcludedIps = implode(',', $array);
    }

    public function onAfterWrite()
    {
        if (self::$loop_count < 3) {
            self::$loop_count++;
            $this->getUnderConstructionCalculatedValues()->CreateFiles();
            if ($this->owner->isChanged('UnderConstructionOnOff')) {
                if ($this->owner->UnderConstructionOnOff === 'Offline') {
                    $task = Injector::inst()->get(GoOffline::class);
                } elseif ($this->owner->UnderConstructionOnOff === 'Online') {
                    $task = Injector::inst()->get(GoOnline::class);
                }
                $this->owner->UnderConstructionOutcome = $task->run(null);
                $this->owner->write();
            }
            if( ! $this->getUnderConstructionCalculatedValues()->UnderConstructionIsReady()) {
                $this->owner->UnderConstructionOutcome = 'Could not create offline files.';
            }
        }
    }



}
