oc.registerControl('user-notifications', class extends oc.ControlBase {
    init() {
        this.$form = this.element.closest('form');
        this.$list = this.$form.querySelector('ul.notifications');
    }

    connect() {
        this.listen('click', '[data-notifications-load-more]', this.loadMoreNotifications);
        this.listen('click', '[data-notifications-mark-read-all]', this.markNotificationsAsRead);
        oc.Events.on(document, 'click', '[data-notifications-toggle]', this.proxy(this.toggleNotificationsPopover));
    }

    disconnect() {
        oc.Events.off(document, 'click', '[data-notifications-toggle]', this.proxy(this.toggleNotificationsPopover));
    }

    toggleNotificationsPopover() {
        var isActive = this.$form.classList.contains('active');
        if (isActive) {
            this.$form.classList.remove('active');
            return;
        }

        this.$form.classList.add('active');
        oc.request(this.element, 'onLoadNotifications', {
            update: { '@notifications-list': '#notificationsContent' }
        });
    }

    loadMoreNotifications() {
        var height = this.$list.scrollHeight;

        oc.request(this.element, 'onLoadMoreNotifications', {
            update: { '@notifications-list': '#notificationsContent' }
        })
        .done(function() {
            this.$list.scrollTo({ top: height });
        });
    }

    markNotificationsAsRead() {
        oc.request(this.element, 'onMarkAllNotificationsAsRead', {
            update: { '@notifications-list': '#notificationsContent' }
        });
    }
});
