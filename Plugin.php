<?php namespace RainLab\UserPlus;

use Yaml;
use File;
use System\Classes\PluginBase;
use RainLab\User\Models\User as UserModel;
use RainLab\Notify\Models\Notification as NotificationModel;
use RainLab\User\Controllers\Users as UsersController;
use RainLab\Notify\NotifyRules\SaveDatabaseAction;
use RainLab\User\Classes\UserEventBase;

/**
 * UserPlus Plugin Information File
 */
class Plugin extends PluginBase
{

    public $require = ['RainLab.User', 'RainLab.Location', 'RainLab.Notify'];

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
        $this->extendUserModel();
        $this->extendUsersController();
        $this->extendSaveDatabaseAction();
        $this->extendUserEventBase();
    }

    public function registerComponents()
    {
        return [
            \RainLab\UserPlus\Components\Notifications::class => 'notifications',
        ];
    }

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

            $model->implement[] = 'RainLab.Location.Behaviors.LocationModel';

            $model->morphMany['notifications'] = [
                NotificationModel::class,
                'name' => 'notifiable',
                'order' => 'created_at desc'
            ];
        });
    }

    protected function extendUsersController()
    {
        UsersController::extendFormFields(function($widget) {
            // Prevent extending of related form instead of the intended User form
            if (!$widget->model instanceof UserModel) {
                return;
            }

            $configFile = plugins_path('rainlab/userplus/config/profile_fields.yaml');
            $config = Yaml::parse(File::get($configFile));
            $widget->addTabFields($config);
        });
    }

    public function registerNotificationRules()
    {
        return [
            'events' => [],
            'actions' => [],
            'conditions' => [
                \RainLab\UserPlus\NotifyRules\UserLocationAttributeCondition::class
            ],
            'presets' => '$/rainlab/userplus/config/notify_presets.yaml',
        ];
    }

    protected function extendUserEventBase()
    {
        if (!class_exists(UserEventBase::class)) {
            return;
        }

        UserEventBase::extend(function($event) {
            $event->conditions[] = \RainLab\UserPlus\NotifyRules\UserLocationAttributeCondition::class;
        });
    }

    protected function extendSaveDatabaseAction()
    {
        if (!class_exists(SaveDatabaseAction::class)) {
            return;
        }

        SaveDatabaseAction::extend(function ($action) {
            $action->addTableDefinition([
                'label' => 'User activity',
                'class' => UserModel::class,
                'param' => 'user'
            ]);
        });
    }
}
