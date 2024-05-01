<?php namespace RainLab\UserPlus\Behaviors;

use System\Classes\ModelBehavior;

/**
 * UserPlusModel extension adds user extensions to the model
 *
 * Usage in the model class definition:
 *
 *     public $implement = [\RainLab\UserPlus\Behaviors\UserPlusModel::class];
 *
 */
class UserPlusModel extends ModelBehavior
{
    /**
     * __construct
     */
    public function __construct($model)
    {
        parent::__construct($model);

        $model->addFillable([
            'address_line1',
            'address_line2',
            'company',
            'phone',
            'city',
            'zip',
        ]);

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
            \RainLab\UserPlus\Models\UserNotification::class,
            'order' => 'created_at desc'
        ];
    }

    /**
     * getStreetAddressAttribute
     */
    public function getStreetAddressAttribute()
    {
        return "{$this->model->address_line1}\n{$this->model->address_line2}";
    }

    /**
     * setStreetAddressAttribute
     */
    public function setStreetAddressAttribute($address)
    {
        $parts = explode("\n", $address, 2);
        $this->model->attributes['address_line1'] = $parts[0];
        $this->model->attributes['address_line2'] = $parts[1] ?? '';
    }
}
