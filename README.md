# User Plus+ plugin

This plugin extends the [User plugin](http://octobercms.com/plugin/rainlab-user) with extra profile fields and features.

* Adds the following extra fields to a user: `company`, `phone`, `address_line1`, `address_line2`, `city`, `zip`, `state_id`, `country_id`.
* A user can belong to a Country and/or State, sourced from the [Location plugin](http://octobercms.com/plugin/rainlab-location).

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
    View notifications
</button>
```

You may also display a counter with the `notifications.hasUnread` check.

```twig
{% if notifications.hasUnread %}
    <span class="counter">{{ notifications.unreadCount }}</span>
{% endif %}
```

### Potential features

* A user can befriend other users via a friendship system.
* A user can earn "Experience Points" by performing predefined activities.

> Note these features may or may not be implemented in the future, but act only as an indicator of the plugin's potential.

### License

This plugin is an official extension of the October CMS platform and is free to use if you have a platform license. See [EULA license](LICENSE.md) for more details.
