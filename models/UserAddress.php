<?php namespace RainLab\UserPlus\Models;

use Model;
use RainLab\User\Models\User;

/**
 * UserAddress Model
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $full_name
 * @property string $company
 * @property string $phone
 * @property string $address_formatted
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
        'first_name' => 'required',
        'last_name' => 'required',
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
                'city' => $this->city,
                'zip' => $this->zip,
                'state_id' => $this->state_id,
                'country_id' => $this->country_id,
            ]);
        }
    }

    /**
     * getFullNameAttribute
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * getAddressFormattedAttribute
     */
    public function getAddressFormattedAttribute()
    {
        $addressFormat = ':address_line1, :city :state_name :zip, :country_name (:first_name :last_name)';

        $string = strtr($addressFormat, [
            ':first_name' => $this->first_name,
            ':last_name' => $this->last_name,
            ':address_line1' => $this->address_line1,
            ':city' => $this->city,
            ':zip' => $this->zip,
            ':state_name' => $this->state?->name,
            ':state_code' => $this->state?->code,
            ':country_name' => $this->country?->name,
            ':country_code' => $this->country?->code,
        ]);

        $string = preg_replace('/\s+/', ' ', $string);

        return $string;
    }
}
