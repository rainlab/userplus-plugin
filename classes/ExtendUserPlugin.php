<?php namespace RainLab\UserPlus\Classes;

use Config;
use October\Rain\Extension\Container as ExtensionContainer;

/**
 * ExtendUserPlugin
 */
class ExtendUserPlugin
{
    /**
     * subscribe
     */
    public function subscribe($events)
    {
        $this->extendUserModel();

        $events->listen('user.users.extendPreviewTabs', [static::class, 'extendPreviewTabs']);

        $events->listen('backend.form.extendFields', [static::class, 'extendFormFields']);

        $events->listen('backend.list.extendColumns', [static::class, 'extendListColumns']);

        $events->listen('backend.filter.extendScopes', [static::class, 'extendFilterScopes']);
    }

    /**
     * extendUserModel
     */
    public function extendUserModel()
    {
        ExtensionContainer::extendClass(\RainLab\User\Models\User::class, static function($model) {
            $model->implementClassWith(\RainLab\Location\Behaviors\LocationModel::class);
            $model->implementClassWith(\RainLab\UserPlus\Behaviors\UserPlusModel::class);
        });
    }

    /**
     * extendPreviewTabs
     */
    public function extendPreviewTabs()
    {
        return [
            "Profile" => $this->checkUseAddressBook()
                ? '$/rainlab/userplus/partials/_user_address_book.php'
                : '$/rainlab/userplus/partials/_user_profile.php'
        ];
    }

    /**
     * extendFormFields
     */
    public function extendFormFields(\Backend\Widgets\Form $widget)
    {
        if ($widget->isNested || !$this->checkControllerMatchesUser($widget)) {
            return;
        }

        if ($this->checkUseAddressBook()) {
            $addressBook = $widget->addTabField('addresses')->displayAs('relation')->tab("Address Book")->span('full')
                ->controller([
                    'label' => 'Address',
                    'list' => '$/rainlab/userplus/models/useraddress/columns.yaml',
                    'form' => '$/rainlab/userplus/models/useraddress/fields.yaml',
                ]);

            if ($widget->getContext() !== 'preview') {
                $addressBook->label('Define addresses for this user, these can be chosen to prefill locations.');
            }
        }
        else {
            $widget->addTabField('company', 'Company')->tab("Profile")->span('full');
            $widget->addTabField('phone', 'Phone')->tab("Profile")->span('full');
            $widget->addTabField('street_address', 'Street Address')->displayAs('textarea')->size('tiny')->tab("Profile")->span('full');
            $widget->addTabField('city', 'City')->tab("Profile")->span('auto');
            $widget->addTabField('zip', 'Zip')->tab("Profile")->span('auto');
            $widget->addTabField('country', 'Country')->tab("Profile")->span('auto')->displayAs('dropdown')->placeholder("-- select state --");
            $widget->addTabField('state', 'State')->tab("Profile")->span('auto')->displayAs('dropdown')->dependsOn('country')->placeholder("-- select state --");
        }
    }

    /**
     * extendListColumns
     */
    public function extendListColumns(\Backend\Widgets\Lists $widget)
    {
        if (!$this->checkControllerMatchesUser($widget)) {
            return;
        }

        $widget->defineColumn('company', "Company")->after('email')->searchable();
        $widget->defineColumn('phone', "Phone")->after('email')->searchable();
        $widget->defineColumn('city', "City")->after('email')->searchable()->invisible();
        $widget->defineColumn('zip', "Zip")->after('email')->searchable()->invisible();
        $widget->defineColumn('state', "State")->after('email')->invisible()->relation('state')->select('name');
        $widget->defineColumn('country', "Country")->after('email')->invisible()->relation('country')->select('name');
    }

    /**
     * extendFilterScopes
     */
    public function extendFilterScopes(\Backend\Widgets\Filter $widget)
    {
        if (!$this->checkControllerMatchesUser($widget)) {
            return;
        }

        $widget->defineScope('country', "Country")->after('created_at')->displayAs('group')->emptyOption("Unspecified");
        $widget->defineScope('state', "State")->after('created_at')->displayAs('group')->optionsMethod('getStateOptionsForFilter')->dependsOn('country')->emptyOption("Unspecified");
    }

    /**
     * checkUseAddressBook
     */
    protected function checkUseAddressBook(): bool
    {
        return Config::get('rainlab.userplus::use_address_book', true);
    }

    /**
     * checkControllerMatchesUser
     */
    protected function checkControllerMatchesUser($widget): bool
    {
        return $widget->getController() instanceof \RainLab\User\Controllers\Users &&
            $widget->getModel() instanceof \RainLab\User\Models\User;
    }
}
