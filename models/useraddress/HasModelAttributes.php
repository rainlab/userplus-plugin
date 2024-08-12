<?php namespace RainLab\UserPlus\Models\UserAddress;

/**
 * HasModelAttributes
 *
 * @property string $full_name
 * @property string $street_address
 * @property string $address_formatted
 *
 * @package rainlab\userplus
 * @author Alexey Bobkov, Samuel Georges
 */
trait HasModelAttributes
{
    /**
     * getFullNameAttribute
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * getStreetAddressAttribute
     */
    public function getStreetAddressAttribute()
    {
        return "{$this->address_line1}\n{$this->address_line2}";
    }

    /**
     * setStreetAddressAttribute
     */
    public function setStreetAddressAttribute($address)
    {
        $parts = explode("\n", $address, 2);
        $this->attributes['address_line1'] = $parts[0];
        $this->attributes['address_line2'] = $parts[1] ?? '';
    }

    /**
     * getAddressFormattedAttribute
     */
    public function getAddressFormattedAttribute()
    {
        if ($this->first_name || $this->last_name) {
            $addressFormat = ':address, :city :state_name :zip, :country_name (:first_name :last_name)';
        }
        else {
            $addressFormat = ':address, :city :state_name :zip, :country_name';
        }

        $address = $this->address_line1;
        if ($this->address_line2) {
            $address .= ', ' . $this->address_line2;
        }

        $string = strtr($addressFormat, [
            ':first_name' => $this->first_name,
            ':last_name' => $this->last_name,
            ':address' => $address,
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
