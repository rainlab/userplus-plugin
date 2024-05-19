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

        // User

        $events->listen('rainlab.user.view.extendPreviewTabs', [static::class, 'extendPreviewTabs']);

        $events->listen('backend.form.extendFields', [static::class, 'extendUserFormFields']);

        $events->listen('backend.list.extendColumns', [static::class, 'extendUserListColumns']);

        $events->listen('backend.filter.extendScopes', [static::class, 'extendUserFilterScopes']);

        // Settings

        $this->extendSettingModel();

        $events->listen('backend.form.extendFields', [static::class, 'extendSettingFormFields']);
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
     * extendSettingModel
     */
    public function extendSettingModel()
    {
        ExtensionContainer::extendClass(\RainLab\User\Models\Setting::class, static function($model) {
            $model->bindEvent('model.initSettingsData', function() use ($model) {
                $model->use_address_book = Config::get('rainlab.userplus::use_address_book', true);
            });
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
     * extendSettingFormFields
     */
    public function extendSettingFormFields(\Backend\Widgets\Form $widget)
    {
        if (!$this->checkControllerMatchesSetting($widget)) {
            return;
        }

        $widget->addTabField('use_address_book', 'Use Address Book')->displayAs('switch')->tab("Profile")->span('full')
            ->comment("Allow users to manage multiple addresses, otherwise users can provide only a single address.");
    }

    /**
     * extendUserFormFields
     */
    public function extendUserFormFields(\Backend\Widgets\Form $widget)
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
     * extendUserListColumns
     */
    public function extendUserListColumns(\Backend\Widgets\Lists $widget)
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
     * extendUserFilterScopes
     */
    public function extendUserFilterScopes(\Backend\Widgets\Filter $widget)
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
        return \RainLab\User\Models\Setting::get('use_address_book', true);
    }

    /**
     * checkControllerMatchesUser
     */
    protected function checkControllerMatchesUser($widget): bool
    {
        return $widget->getController() instanceof \RainLab\User\Controllers\Users &&
            $widget->getModel() instanceof \RainLab\User\Models\User;
    }

    /**
     * checkControllerMatchesSetting
     */
    protected function checkControllerMatchesSetting($widget): bool
    {
        return $widget->getController() instanceof \System\Controllers\Settings &&
            $widget->getModel() instanceof \RainLab\User\Models\Setting;
    }
}
