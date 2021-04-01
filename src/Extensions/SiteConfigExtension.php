<?php

namespace Sunnysideup\UnderConstruction\Extensions;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;

use SilverStripe\Forms\DropdownField;

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
        'UnderConstructionBackgroundColour' => 'Varchar(200)',
        'UnderConstructionForegroundColour' => 'Varchar(200)',
    ];

    private static $has_one = [
        'UnderConstructionImage' => Image::class,
    ];

    private static $defaults = [
        'UnderConstructionBackgroundColour' => '#000',
        'UnderConstructionForegroundColour' => '#fff',
        'UnderConstructionOnOff' => 'Online',
        'UnderConstructionMinutesOffline' => 20,
        'UnderConstructionTitle' => 'Sorry, we are offline for an upgrade.',
        'UnderConstructionSubTitle' => 'Please come back soon.',
    ];

    private static $owns = [
        'UnderConstructionImage',
    ];

    private $underConstructionCalculatedValues;

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName('UnderConstructionOutcome');
        $fields->addFieldsToTab(
            'Root.Offline',
            [
                ReadonlyField::create(
                    'UnderConstructionOutcome',
                    'Status ...'
                )
                    ->setDescription('Was the last action successful? Are there any worries?'),
                OptionsetField::create(
                    'UnderConstructionOnOff',
                    'Is the site Online or Offline',
                    ['Online' => 'Online', 'Offline' => 'Offline']
                )
                    ->setDescription('Make the site go Online / Offline. Please use with care!'),
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
                DropdownField::create(
                    'UnderConstructionForegroundColour',
                    'Text Colour',
                    Config::inst()->get(CalculatedValues::class, 'under_construction_fg_options')
                ),
                DropdownField::create(
                    'UnderConstructionBackgroundColour',
                    'Text Colour',
                    Config::inst()->get(CalculatedValues::class, 'under_construction_bg_options')
                ),

            ]
        );
        if ($this->owner->UnderConstructionOnOff === 'Offline') {
            $fields->replaceField(
                'UnderConstructionExcludedIps',
                ReadonlyField::create('UnderConstructionExcludedIps', 'Allowed IP Addresses')
                    ->setDescription('This can only be changed when the site is Online.')
            );
        }
        if ($this->getUnderConstructionCalculatedValues()->UnderConstructionIsReady()) {
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
                    ),
                ),
            ],
            'UnderConstructionMinutesOffline',
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
        $currentController = @Controller::curr();
        if ($currentController && $currentController->getRequest()) {
            $currentIp = $currentController->getRequest()->getIp();
            $array = explode(',', $this->owner->UnderConstructionExcludedIps);
            $array = array_map('trim', $array);
            $array = array_filter($array);
            if ($currentIp) {
                if (! in_array($currentIp, $array, true)) {
                    $array[] = $currentIp;
                }
            }
            $this->owner->UnderConstructionExcludedIps = implode(', ', $array);
        }
    }

    public function onAfterWrite()
    {
        if (self::$loop_count < 2) {
            ++self::$loop_count;
            $this->CreateFiles();
            // 2 = only real changes.
            if ($this->owner->isChanged('UnderConstructionOnOff', 2)) {
                $task = null;
                if ($this->owner->UnderConstructionOnOff === 'Offline') {
                    $task = Injector::inst()->get(GoOffline::class);
                } elseif ($this->owner->UnderConstructionOnOff === 'Online') {
                    $task = Injector::inst()->get(GoOnline::class);
                }
                if ($task) {
                    $this->owner->UnderConstructionOutcome = $task->run(null);
                    $this->owner->write();
                }
            }
        }
        register_shutdown_function([$this->owner, 'CreateFiles']);
    }

    public function CreateFiles()
    {
        $this->getUnderConstructionCalculatedValues()->CreateFiles();
    }

    public function requireDefaultRecords()
    {
        if (! Director::is_cli()) {
            $this->getUnderConstructionCalculatedValues()->CreateDirAndTest();
        }
    }
}
