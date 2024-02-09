<?php namespace RainLab\UserPlus\Models;

use Model;
use Markdown;
use RainLab\User\Models\User;

/**
 * Notification Model stored in the database
 */
class Notification extends Model
{
    use \October\Rain\Database\Traits\BaseIdentifier;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'rainlab_user_notifications';

    /**
     * @var array Guarded fields
     */
    protected $guarded = [];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array List of attribute names which are json encoded and decoded from the database.
     */
    protected $jsonable = ['data'];

    /**
     * @var array List of datetime attributes to convert to an instance of Carbon/DateTime objects.
     */
    protected $dates = ['read_at'];

    /**
     * appends to the model's array form.
     */
    protected $appends = ['parsed_body'];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'user' => User::class,
    ];

    /**
     * markAsRead marks the notification as read.
     */
    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->forceFill(['read_at' => $this->freshTimestamp()])->save();
        }
    }

    /**
     * getIsReadAttribute determines if a notification has been read.
     */
    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * getIsUnreadAttribute determines if a notification has not been read.
     */
    public function getIsUnreadAttribute(): bool
    {
        return $this->read_at === null;
    }

    /**
     * scopeApplyUnread get the user unread notifications.
     */
    public function scopeApplyUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * scopeApplyRead gets the user read notifications.
     */
    public function scopeApplyRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * getParsedBodyAttribute get the parsed body of the announcement.
     */
    public function getParsedBodyAttribute(): string
    {
        return Markdown::parse($this->body);
    }
}
