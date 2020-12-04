<?php

namespace Sunnysideup\UnderConstruction\Extensions\SiteConfigExtension;

use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\FieldList;
use Page;
use Symbiote\SortableMenu\SortableMenuExtensionException;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\DataList;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use SilverStripe\CMS\Model\SiteTree;
use Symbiote\Multisites\Model\Site;

class SiteConfigExtension extends DataExtension
{
    private const FILE_NAME = 'offline.php';

    private static $db = [
        'UnderConstructionUntil' => 'DateTime',
        'UnderConstructionTitle' => 'Varchar',
        'UnderConstructionSubTitle' => 'Varchar',
    ];

    private static $has_one = [
        'UnderConstructionImage' => 'Image',
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $owner = $this->owner;
        $publicUrl = Director::AbsoluteLink(self::FILE_NAME);
        $fields->addFieldsToTab(
            'Root.Offline',
            [
                $owner->dataFieldByName('UnderConstructionUntil'),
                $owner->dataFieldByName('UnderConstructionTitle'),
                $owner->dataFieldByName('UnderConstructionSubTitle'),
                $owner->dataFieldByName('BackgroundImage'),
                ReadonlyField::create(
                    'UnderConstructionPublicUrl',
                    'View File',
                    DBField::create_field(
                        'HTMLText',
                        '<a href="'.$publicUrl.'">'.$publicUrl.'</a>'
                    )
                ),
            ]
        );
        return $fields;
    }

    public function UnderConstructionImageName() : string
    {
        $name = $this->UnderConstructionImage();
        $extension =  \array_pop(explode('.', $name));

        return str_replace('.php', '.' . $extension, self::FILE_NAME);
    }


    public function onAfterWrite()
    {
        parent::onAfterWrite();
        $html = $this->owner->renderWith('Sunnysideup\\UnderConstruction\\UnderConstruction');
        $fileName = $publicDir. '/' . self::FILE_NAME ;
        file_put_contents($fileName, $html);
        $imageName = $publicDir. '/' .  $this->UnderConstructionImageName();
        copy($this->UnderConstructionImage()->Link(), $publicDir . '/' . $imageName);
    }

}
