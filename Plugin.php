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
        $this->extendUserPlugin();
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
     * extendUserPlugin
     */
    protected function extendUserPlugin()
    {
        Event::subscribe(\RainLab\UserPlus\Classes\UserPlusEventHandler::class);

        ExtensionContainer::extendClass(\RainLab\User\Models\User::class, function($model) {
            $model->implementClassWith(\RainLab\Location\Behaviors\LocationModel::class);
            $model->implementClassWith(\RainLab\UserPlus\Behaviors\UserPlusModel::class);
        });
    }
}
