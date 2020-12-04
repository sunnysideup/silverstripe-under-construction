<?php

namespace Sunnysideup\UnderConstruction\Extensions;

use SilverStripe\Control\Controller;
use SilverStripe\Forms\TextField;
use SilverStripe\Control\Director;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\FieldList;
use Page;
use Symbiote\SortableMenu\SortableMenuExtensionException;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\DataList;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use SilverStripe\CMS\Model\SiteTree;

use SilverStripe\AssetAdmin\Forms\UploadField;

use SilverStripe\Assets\Image;
use SilverStripe\Assets\File;
use Symbiote\Multisites\Model\Site;

class SiteConfigExtension extends DataExtension
{
    private const FILE_NAME = 'offline.php';

    private static $db = [
        'UnderConstructionMinutesOffline' => 'Int',
        'UnderConstructionTitle' => 'Varchar',
        'UnderConstructionSubTitle' => 'Varchar',
    ];

    private static $defaults = [
        'UnderConstructionMinutesOffline' => 20,
        'UnderConstructionTitle' => 'Sorry, we are offline for an upgrade.',
        'UnderConstructionSubTitle' => 'Please come back soon.',
    ];

    private static $owns = [
        'UnderConstructionImage',
    ];

    private static $has_one = [
        'UnderConstructionImage' => Image::class,
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $owner = $this->owner;
        $fields->addFieldsToTab(
            'Root.Offline',
            [
                NumericField::create(
                    'UnderConstructionMinutesOffline',
                    'Minutes Offline'
                ),
                TextField::create(
                    'UnderConstructionTitle',
                    'Page Title'
                ),
                TextField::create(
                    'UnderConstructionSubTitle',
                    'Page Sub-Title'
                ),
                UploadField::create(
                    'UnderConstructionImage',
                    'Background Image'
                )
                    ->setFolderName('offline-images')
                    ->setAllowedFileCategories('image')
                    ->setIsMultiUpload(false),
            ]
        );
        $fileName = Controller::join_links(Director::baseFolder(), Director::publicDir(), self::FILE_NAME);
        if(file_exists($fileName)) {
            $publicUrl = Controller::join_links(Director::absoluteBaseURL() , self::FILE_NAME);
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


    public function UnderConstructionImageName() : string
    {
        $imageName = $this->owner->UnderConstructionImage()->getFilename();
        $extension =  File::get_file_extension($imageName);

        return str_replace('.php', '.' . $extension, self::FILE_NAME);
    }


    public function onAfterWrite()
    {
        parent::onAfterWrite();
        $html = $this->owner->renderWith('Sunnysideup\\UnderConstruction\\UnderConstruction');
        $fileName = Controller::join_links(Director::baseFolder(), Director::publicDir(), self::FILE_NAME);
        if(file_exists($fileName)) {
            unlink($fileName);
        }
        file_put_contents($fileName, $html);
        $image = $this->owner->UnderConstructionImage();
        if($image && $image->exists()) {
            $imageName = Controller::join_links(Director::baseFolder(), Director::publicDir(), $this->owner->UnderConstructionImageName());
            if(file_exists($imageName)) {
                unlink($imageName);
            }
            $image->copyFile($imageName);
            // copy($this->UnderConstructionImage()->Link(), $publicDir . '/' . $imageName);
        }
    }

}
