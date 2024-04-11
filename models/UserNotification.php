<?php namespace RainLab\UserPlus\Models;

use Model;
use Markdown;
use RainLab\User\Models\User;

/**
 * UserNotification Model stored in the database
 *
 * @property int $id
 * @property string $baseid
 * @property string $type
 * @property string $body
 * @property string $data
 * @property int $user_id
 * @property \Illuminate\Support\Carbon $read_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon $created_at
 *
 * @package rainlab\userplus
 * @author Alexey Bobkov, Samuel Georges
 */
class UserNotification extends Model
{
    use \October\Rain\Database\Traits\BaseIdentifier;

    /**
     * @var string table associated with the model
     */
    public $table = 'rainlab_user_notifications';

    /**
     * @var array jsonable attribute names that are json encoded and decoded from the database
     */
    protected $jsonable = ['data'];

    /**
     * @var array dates attributes that should be mutated to dates
     */
    protected $dates = ['read_at'];

    /**
     * appends to the model's array form.
     */
    protected $appends = ['parsed_body'];

    /**
     * @var array belongsTo relation
     */
    public $belongsTo = [
        'user' => User::class,
    ];

    /**
     * createRecord adds a notification for a user
     */
    public static function createRecord($userId, $type, $body, $data = null)
    {
        $obj = new static;
        $obj->user_id = $userId;
        $obj->type = $type;
        $obj->body = $body;
        if (is_array($data)) {
            $obj->data = $data;
        }
        $obj->save();

        return $obj;
    }

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
