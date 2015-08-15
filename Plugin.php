<?php namespace RainLab\UserPlus;

use Yaml;
use File;
use System\Classes\PluginBase;
use RainLab\User\Models\User as UserModel;
use RainLab\User\Controllers\Users as UsersController;

/**
 * UserPlus Plugin Information File
 */
class Plugin extends PluginBase
{

    public $require = ['RainLab.User', 'RainLab.Location'];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'rainlab.userplus::lang.plugin.name',
            'description' => 'rainlab.userplus::lang.plugin.description',
            'author'      => 'Alexey Bobkov, Samuel Georges',
            'icon'        => 'icon-user-plus',
            'homepage'    => 'https://github.com/rainlab/userplus-plugin'
        ];
    }

    public function boot()
    {
        UserModel::extend(function($model) {
            $model->addFillable([
                'phone',
                'mobile',
                'company',
                'street_addr',
                'city',
                'zip'
            ]);

            $model->implement[] = 'RainLab.Location.Behaviors.LocationModel';
        });

        UsersController::extendFormFields(function($widget) {
            $configFile = __DIR__ . '/config/profile_fields.yaml';
            $config = Yaml::parse(File::get($configFile));
            $widget->addTabFields($config);
        });
    }

}
