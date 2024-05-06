# User Plus+ plugin

This plugin extends the [User plugin](https://octobercms.com/plugin/rainlab-user) with extra profile fields and features.

- Adds the following extra fields to a user: `company`, `phone`, `address_line1`, `address_line2`, `city`, `zip`, `state_id`, `country_id`.
- Adds an Address Book to store multiple addresses for a user.
- A user can belong to a Country and/or State, sourced from the [Location plugin](https://octobercms.com/plugin/rainlab-location).

View this plugin on the October CMS marketplace:

- https://octobercms.com/plugin/rainlab-userplus

### Address Book Component

The `addressBook` component is used to manage multiple addresses for a user. Enable or disable the address book using the **System → User Settings → Profile** page.

To see an example of the usage, we recommend installing this plugin with the `RainLab.Vanilla` theme.

- https://github.com/rainlab/vanilla-theme

### Notifications Component

The `notifications` component is used to display notifications assigned to the user, it allows them to mark notifications as read. It is best to add this component to the layout, the component will render the popover in a hidden state.

```twig
{% component 'notifications' %}
```

The link to display notifications can be anywhere, and should have the `data-notifications-toggle` attribute to trigger the popover.

```html
<button
    type="button"
    class="btn btn-default"
    data-notifications-toggle>
    View Notifications
</button>
```

You may also display a counter with the `notifications.hasUnread` check.

```twig
{% if notifications.hasUnread %}
    <span class="counter">{{ notifications.unreadCount }}</span>
{% endif %}
```

### License

This plugin is an official extension of the October CMS platform and is free to use if you have a platform license. See [EULA license](LICENSE.md) for more details.
