<?php namespace RainLab\UserPlus\Classes;

use Config;

/**
 * UserPlusEventHandler
 */
class UserPlusEventHandler
{
    /**
     * subscribe
     */
    public function subscribe($events)
    {
        $events->listen('user.users.extendPreviewTabs', [$this, 'extendPreviewTabs']);

        $events->listen('backend.form.extendFields', [$this, 'extendFormFields']);

        $events->listen('backend.list.extendColumns', [$this, 'extendListColumns']);

        $events->listen('backend.filter.extendScopes', [$this, 'extendFilterScopes']);
    }

    /**
     * extendPreviewTabs
     */
    public function extendPreviewTabs()
    {
        return [
            "Profile" => $this->checkUseAddressBook()
                ? '$/rainlab/userplus/partials/_user_address_book.php'
                : '$/rainlab/userplus/partials/_user_profile.php'
        ];
    }

    /**
     * extendFormFields
     */
    public function extendFormFields(\Backend\Widgets\Form $widget)
    {
        if ($widget->isNested || !$this->checkControllerModelMatch($widget)) {
            return;
        }

        if ($this->checkUseAddressBook()) {
            $addressBook = $widget->addTabField('addresses')->displayAs('relation')->tab("Address Book")->span('full')
                ->controller([
                    'label' => 'Address',
                    'list' => '$/rainlab/userplus/models/useraddress/columns.yaml',
                    'form' => '$/rainlab/userplus/models/useraddress/fields.yaml',
                ]);

            if ($widget->getContext() !== 'preview') {
                $addressBook->label('Define addresses for this user, these can be chosen to prefill locations.');
            }
        }
        else {
            $widget->addTabField('company', 'Company')->tab("Profile")->span('full');
            $widget->addTabField('phone', 'Phone')->tab("Profile")->span('full');
            $widget->addTabField('street_address', 'Street Address')->displayAs('textarea')->size('tiny')->tab("Profile")->span('full');
            $widget->addTabField('city', 'City')->tab("Profile")->span('auto');
            $widget->addTabField('zip', 'Zip')->tab("Profile")->span('auto');
            $widget->addTabField('country', 'Country')->tab("Profile")->span('auto')->displayAs('dropdown')->placeholder("-- select state --");
            $widget->addTabField('state', 'State')->tab("Profile")->span('auto')->displayAs('dropdown')->dependsOn('country')->placeholder("-- select state --");
        }
    }

    /**
     * extendListColumns
     */
    public function extendListColumns(\Backend\Widgets\Lists $widget)
    {
        if (!$this->checkControllerModelMatch($widget)) {
            return;
        }

        $widget->defineColumn('company', "Company")->after('email')->searchable();
        $widget->defineColumn('phone', "Phone")->after('email')->searchable();
        $widget->defineColumn('city', "City")->after('email')->searchable()->invisible();
        $widget->defineColumn('zip', "Zip")->after('email')->searchable()->invisible();
        $widget->defineColumn('state', "State")->after('email')->invisible()->relation('state')->select('name');
        $widget->defineColumn('country', "Country")->after('email')->invisible()->relation('country')->select('name');
    }

    /**
     * extendFilterScopes
     */
    public function extendFilterScopes(\Backend\Widgets\Filter $widget)
    {
        if (!$this->checkControllerModelMatch($widget)) {
            return;
        }

        $widget->defineScope('country', "Country")->after('created_at')->displayAs('group')->emptyOption("Unspecified");
        $widget->defineScope('state', "State")->after('created_at')->displayAs('group')->optionsMethod('getStateOptionsForFilter')->dependsOn('country')->emptyOption("Unspecified");
    }

    /**
     * checkUseAddressBook
     */
    protected function checkUseAddressBook(): bool
    {
        return Config::get('rainlab.userplus::use_address_book', true);
    }

    /**
     * checkControllerModelMatch
     */
    protected function checkControllerModelMatch($widget): bool
    {
        return $widget->getController() instanceof \RainLab\User\Controllers\Users &&
            $widget->getModel() instanceof \RainLab\User\Models\User;
    }
}
