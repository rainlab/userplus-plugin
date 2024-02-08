<?php namespace RainLab\UserPlus;

use Yaml;
use File;
use System\Classes\PluginBase;
use RainLab\User\Models\User as UserModel;
use RainLab\User\Controllers\Users as UsersController;
use RainLab\User\Classes\UserEventBase;

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
            \RainLab\UserPlus\Components\Notifications::class => 'notifications',
        ];
    }

    /**
     * extendUserModel
     */
    protected function extendUserModel()
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

            if (!$model->isClassExtendedWith(\RainLab\Location\Behaviors\LocationModel::class)) {
                $model->extendClassWith(\RainLab\Location\Behaviors\LocationModel::class);
            }

            $model->morphMany['notifications'] = [
                NotificationModel::class,
                'name' => 'notifiable',
                'order' => 'created_at desc'
            ];
        });
    }

    /**
     * extendUsersController
     */
    protected function extendUsersController()
    {
        UsersController::extendFormFields(function($widget) {
            // Prevent extending of related form instead of the intended User form
            if (!$widget->model instanceof UserModel) {
                return;
            }

            if ($widget->isNested) {
                return;
            }

            $configFile = plugins_path('rainlab/userplus/config/profile_fields.yaml');
            $config = Yaml::parse(File::get($configFile));
            $widget->addTabFields($config);
        });
    }
}
