<?php namespace RainLab\UserPlus\Components;

use Auth;
use Carbon\Carbon;
use RainLab\User\Models\User;
use Cms\Classes\ComponentBase;
use ApplicationException;

/**
 * Notifications component
 */
class Notifications extends ComponentBase
{
    /**
     * @var int|null countCache for checking number of notifications
     */
    protected $countCache;

    /**
     * componentDetails
     */
    public function componentDetails()
    {
        return [
            'name' => "Notifications Component",
            'description' => "Display user and site-wide notifications"
        ];
    }

    /**
     * defineProperties
     */
    public function defineProperties()
    {
        return [
            'recordsPerPage' => [
                'title' => 'Records Per Page',
                'comment' => 'Number of notifications to display per page',
                'default' => 7
            ],
            'includeAssets' => [
                'title' => 'Include Assets',
                'comment' => 'Inject the JavaScript and Stylesheet used by the default component markup',
                'type' => 'checkbox',
                'default' => true
            ]
        ];
    }

    /**
     * onRun
     */
    public function onRun()
    {
        if (!$this->user()) {
            return;
        }

        if ($this->property('includeAssets')) {
            $this->addCss('assets/css/notifications.css');
            $this->addJs('assets/js/notifications.js');
        }

        $this->prepareVars();
    }

    /**
     * prepareVars
     */
    protected function prepareVars()
    {
        $this->page['notificationToDisplay'] = $this->getRecordCountToDisplay();
    }

    /**
     * user returns the logged in user
     */
    public function user(): ?User
    {
        return Auth::user();
    }

    /**
     * hasUnread
     */
    public function hasUnread()
    {
        return $this->unreadCount() > 0;
    }

    /**
     * unreadCount
     */
    public function unreadCount()
    {
        return $this->countCache ??= $this->getUnreadQuery()->count();
    }

    /**
     * unreadNotifications
     */
    public function unreadNotifications($notificationToDisplay = null)
    {
        if (!$notificationToDisplay) {
            $notificationToDisplay = $this->getRecordCountToDisplay();
        }

        return $this->getUnreadQuery()->paginate($notificationToDisplay);
    }

    /**
     * onLoadNotifications handler
     */
    public function onLoadNotifications()
    {
        $this->prepareVars();
        $this->page['notifications'] = $this->unreadNotifications();
    }

    /**
     * onLoadMoreNotifications handler
     */
    public function onLoadMoreNotifications()
    {
        $notificationToDisplay = $this->getRecordCountToDisplay() + $this->property('recordsPerPage');

        $this->page['notificationToDisplay'] = $notificationToDisplay;
        $this->page['notifications'] = $this->unreadNotifications($notificationToDisplay);
    }

    /**
     * onMarkAllNotificationsAsRead handler
     */
    public function onMarkAllNotificationsAsRead()
    {
        $this->getUnreadQuery()->update(['read_at' => Carbon::now()]);

        $this->prepareVars();

        $this->page['notifications'] = $this->unreadNotifications();

        $this->dispatchBrowserEvent('user:notification-count', ['unreadCount' => 0]);
    }

    /**
     * getRecordCountToDisplay
     */
    protected function getRecordCountToDisplay()
    {
        return ((int) post('records_per_page')) ?: $this->property('recordsPerPage');
    }

    /**
     * getUnreadQuery
     */
    protected function getUnreadQuery()
    {
        if (!$user = Auth::getUser()) {
            throw new ApplicationException('You must be logged in');
        }

        return $user->notifications()->applyUnread();
    }
}
