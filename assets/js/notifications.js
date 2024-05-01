oc.registerControl('user-notifications', class extends oc.ControlBase {
    init() {
        this.isActive = false;
    }

    connect() {
        this.$form = this.element.closest('form');

        this.listen('click', '[data-notifications-load-more]', this.loadMoreNotifications);
        this.listen('click', '[data-notifications-mark-read-all]', this.markNotificationsAsRead);
        oc.Events.on(document, 'click', ':not([data-notifications-toggle])', this.proxy(this.clickOutsideNotificationsPopover));
        oc.Events.on(document, 'click', '[data-notifications-toggle]', this.proxy(this.toggleNotificationsPopover));
    }

    disconnect() {
        oc.Events.off(document, 'click', ':not([data-notifications-toggle])', this.proxy(this.clickOutsideNotificationsPopover));
        oc.Events.off(document, 'click', '[data-notifications-toggle]', this.proxy(this.toggleNotificationsPopover));
    }

    clickOutsideNotificationsPopover(ev) {
        if (!this.isActive) {
            return;
        }

        if (ev.target.closest('[data-control~="user-notifications"]')) {
            return;
        }

        this.toggleNotificationsPopover();
    }

    toggleNotificationsPopover() {
        if (this.isActive) {
            this.isActive = false;
            this.$form.classList.remove('active');
            return;
        }

        this.isActive = true;
        this.$form.classList.add('active');
        oc.request(this.element, 'onLoadNotifications', {
            update: { '@notifications-list': '#notificationsContent' }
        });
    }

    loadMoreNotifications() {
        var height = this.$form.querySelector('ul.notifications')?.scrollHeight;

        oc.request(this.element, 'onLoadMoreNotifications', {
            update: { '@notifications-list': '#notificationsContent' },
            afterUpdate: () => {
                this.$form.querySelector('ul.notifications')?.scrollTo({ top: height });
            }
        });
    }

    markNotificationsAsRead() {
        oc.request(this.element, 'onMarkAllNotificationsAsRead', {
            update: { '@notifications-list': '#notificationsContent' }
        });
    }
});
