<?php namespace RainLab\UserPlus\Models;

use Model;
use RainLab\User\Models\User;

/**
 * UserAddress Model
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $company
 * @property string $phone
 * @property string $address_line1
 * @property string $address_line2
 * @property string $city
 * @property string $zip
 * @property int $state_id
 * @property int $country_id
 * @property int $user_id
 * @property bool $is_default
 * @property bool $is_business
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon $created_at
 *
 * @package rainlab\userplus
 * @author Alexey Bobkov, Samuel Georges
 */
class UserAddress extends Model
{
    use \RainLab\UserPlus\Models\UserAddress\HasModelAttributes;
    use \RainLab\Location\Traits\LocationModel;
    use \October\Rain\Database\Traits\Nullable;
    use \October\Rain\Database\Traits\SoftDelete;
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Defaultable;

    /**
     * @var string table used by the model
     */
    protected $table = 'user_addresses';

    /**
     * @var array rules for validation
     */
    public $rules = [
        'address_line1' => 'required',
        'city' => 'required',
        'zip' => 'required',
        'country' => 'required',
    ];

    /**
     * @var array fillable fields
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'company',
        'phone',
        'address_line1',
        'address_line2',
        'city',
        'zip',
        'state_id',
        'country_id',
        'is_business',
        'is_default',
    ];

    /**
     * @var array nullable attribute names which should be set to null when empty.
     */
    protected $nullable = [
        'state_id',
        'is_business',
        'is_default'
    ];

    /**
     * @var array belongsTo relations
     */
    public $belongsTo = [
        'user' => User::class
    ];

    /**
     * afterSave
     */
    public function afterSave()
    {
        if ($this->is_default && $this->user_id) {
            $this->user()->update([
                'company' => $this->company,
                'address_line1' => $this->address_line1,
                'address_line2' => $this->address_line2,
                'city' => $this->city,
                'zip' => $this->zip,
                'state_id' => $this->state_id,
                'country_id' => $this->country_id,
                'phone' => $this->phone,
            ]);
        }
    }
}
