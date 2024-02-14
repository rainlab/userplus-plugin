<?php namespace RainLab\UserPlus;

use Yaml;
use File;
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
        Event::listen('backend.form.extendFields', function($widget) {
            if (
                !$widget->getController() instanceof \RainLab\User\Controllers\Users ||
                !$widget->getModel() instanceof \RainLab\User\Models\User ||
                $widget->isNested
            ) {
                return;
            }

            $configFile = plugins_path('rainlab/userplus/config/profile_fields.yaml');
            $config = Yaml::parse(File::get($configFile));
            $widget->addTabFields($config);
        });
    }
}
