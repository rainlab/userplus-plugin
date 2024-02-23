<?php namespace RainLab\UserPlus;

use Event;
use System\Classes\PluginBase;
use October\Rain\Extension\Container as ExtensionContainer;

/**
 * Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var array require plugins
     */
    public $require = [
        'RainLab.User',
        'RainLab.Location'
    ];

    /**
     * pluginDetails
     */
    public function pluginDetails()
    {
        return [
            'name' => "User Plus+",
            'description' => "Adds profile fields to users.",
            'author' => 'Alexey Bobkov, Samuel Georges',
            'icon' => 'icon-user-plus',
            'homepage' => 'https://github.com/rainlab/userplus-plugin'
        ];
    }

    /**
     * boot
     */
    public function boot()
    {
        $this->extendUserModel();
        $this->extendUsersController();
    }

    /**
     * registerComponents
     */
    public function registerComponents()
    {
        return [
            \RainLab\UserPlus\Components\AddressBook::class => 'addressBook',
            \RainLab\UserPlus\Components\Notifications::class => 'notifications',
        ];
    }

    /**
     * extendUserModel
     */
    protected function extendUserModel()
    {
        ExtensionContainer::extendClass(\RainLab\User\Models\User::class, function($model) {
            $model->addFillable([
                'company',
                'phone',
                'city',
                'zip',
            ]);

            $model->implementClassWith(\RainLab\Location\Behaviors\LocationModel::class);

            $model->hasOne['primary_address'] = [
                \RainLab\UserPlus\Models\UserAddress::class,
                'conditions' => 'is_default = true',
                'default' => ['is_default' => true]
            ];

            $model->hasMany['addresses'] = [
                \RainLab\UserPlus\Models\UserAddress::class,
                'order' => 'is_default desc'
            ];

            $model->hasMany['notifications'] = [
                \RainLab\UserPlus\Models\Notification::class,
                'order' => 'created_at desc'
            ];
        });
    }

    /**
     * extendUsersController
     */
    protected function extendUsersController()
    {
        Event::listen('user.users.extendPreviewTabs', function() {
            return [
                "Profile" => '$/rainlab/userplus/partials/_user_profile.php',
            ];
        });

        Event::listen('backend.form.extendFields', function(\Backend\Widgets\Form $widget) {
            if (
                $widget->isNested ||
                !$widget->getController() instanceof \RainLab\User\Controllers\Users ||
                !$widget->getModel() instanceof \RainLab\User\Models\User
            ) {
                return;
            }

            $widget->addTabField('company', 'Company')->tab("Profile")->span('full');
            $widget->addTabField('phone', 'Phone')->tab("Profile")->span('full');
            // $widget->addTabField('city', 'City')->tab("Profile")->span('auto');
            // $widget->addTabField('zip', 'Zip')->tab("Profile")->span('auto');
            // $widget->addTabField('country', 'Country')->tab("Profile")->span('auto')->displayAs('dropdown')->placeholder("-- select state --");
            // $widget->addTabField('state', 'State')->tab("Profile")->span('auto')->displayAs('dropdown')->dependsOn('country')->placeholder("-- select state --");

            if ($widget->getContext() === 'preview') {
                // $widget->addTabField('primary_address[first_name]', "First Name")->tab("Primary Address")->span('auto');
                // $widget->addTabField('primary_address[last_name]', "Last Name")->tab("Primary Address")->span('auto');
                // $widget->addTabField('primary_address[company]', "Company")->tab("Primary Address")->span('auto');
                // $widget->addTabField('primary_address[phone]', "Phone")->tab("Primary Address")->span('auto');
                $widget->addTabField('primary_address[address_line1]', "Address")->tab("Primary Address")->span('full')->displayAs('textarea')->size('tiny')->cssClass('pb-2');
                $widget->addTabField('primary_address[is_business]', "Location is a business address")->tab("Primary Address")->span('full')->displayAs('checkbox')->cssClass('pb-2');
                $widget->addTabField('primary_address[city]', "City")->tab("Primary Address")->span('auto');
                $widget->addTabField('primary_address[zip]', "Zip / Postal Code")->tab("Primary Address")->span('auto');
                $widget->addTabField('primary_address[country]', "Country")->tab("Primary Address")->span('auto')->displayAs('dropdown');
                $widget->addTabField('primary_address[state]', "State")->tab("Primary Address")->span('auto')->displayAs('dropdown');
            }
        });

        Event::listen('backend.list.extendColumns', function(\Backend\Widgets\Lists $widget) {
            if (
                !$widget->getController() instanceof \RainLab\User\Controllers\Users ||
                !$widget->getModel() instanceof \RainLab\User\Models\User
            ) {
                return;
            }

            $widget->defineColumn('company', "Company")->after('email')->searchable();
            $widget->defineColumn('phone', "Phone")->after('email')->searchable();
            $widget->defineColumn('city', "City")->after('email')->searchable()->invisible();
            $widget->defineColumn('zip', "Zip")->after('email')->searchable()->invisible();
            $widget->defineColumn('state', "State")->after('email')->invisible()->relation('state')->select('name');
            $widget->defineColumn('country', "Country")->after('email')->invisible()->relation('country')->select('name');
        });

        Event::listen('backend.filter.extendScopes', function(\Backend\Widgets\Filter $widget) {
            if (
                !$widget->getController() instanceof \RainLab\User\Controllers\Users ||
                !$widget->getModel() instanceof \RainLab\User\Models\User
            ) {
                return;
            }

            $widget->defineScope('country', "Country")->after('created_at')->displayAs('group')->emptyOption("Unspecified");
            $widget->defineScope('state', "State")->after('created_at')->displayAs('group')->optionsMethod('getStateOptionsForFilter')->dependsOn('country')->emptyOption("Unspecified");
        });
    }
}
