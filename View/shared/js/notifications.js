(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const root = document.querySelector('[data-notifs-root]');
        if (!root) {
            return;
        }

        const dropdownEl = root;
        const bell = root.querySelector('[data-bs-toggle="dropdown"]');
        const badge = root.querySelector('[data-notifs-badge]');
        const list = root.querySelector('[data-notifs-list]');
        const countLabel = root.querySelector('[data-notifs-count-label]');
        const markAllForm = root.querySelector('[data-notifs-markall-form]');

        if (!bell || !list) {
            return;
        }

        const endpoint = root.getAttribute('data-endpoint') || '';
        const markEndpoint = root.getAttribute('data-mark-endpoint') || '';
        const markAllEndpoint = root.getAttribute('data-mark-all-endpoint') || '';
        const pageUrl = root.getAttribute('data-page-url') || '';

        let loaded = false;
        let loading = false;
        let cache = [];

        const escapeHtml = function (value) {
            return String(value == null ? '' : value).replace(/[&<>"']/g, function (ch) {
                switch (ch) {
                    case '&': return '&amp;';
                    case '<': return '&lt;';
                    case '>': return '&gt;';
                    case '"': return '&quot;';
                    case "'": return '&#39;';
                    default: return ch;
                }
            });
        };

        const inferType = function (lien) {
            const link = String(lien || '');
            if (link.indexOf('/demandes') !== -1 || link.indexOf('/services') !== -1) {
                return { key: 'demandes', icon: 'fa-solid fa-envelope-open-text' };
            }
            if (link.indexOf('/rendezvous') !== -1) {
                return { key: 'rendezvous', icon: 'fa-solid fa-calendar-check' };
            }
            if (link.indexOf('/evenements') !== -1 || link.indexOf('/events') !== -1) {
                return { key: 'evenements', icon: 'fa-solid fa-calendar-day' };
            }
            if (link.indexOf('/certifications') !== -1) {
                return { key: 'certifications', icon: 'fa-solid fa-graduation-cap' };
            }
            if (link.indexOf('/documents') !== -1) {
                return { key: 'documents', icon: 'fa-solid fa-folder-open' };
            }
            return { key: 'default', icon: 'fa-regular fa-bell' };
        };

        const parseDate = function (value) {
            if (!value) {
                return null;
            }
            const normalized = String(value).replace(' ', 'T');
            const date = new Date(normalized);
            if (isNaN(date.getTime())) {
                return null;
            }
            return date;
        };

        const formatTimeAgo = function (value) {
            const date = parseDate(value);
            if (!date) {
                return '';
            }
            const seconds = Math.max(0, Math.floor((Date.now() - date.getTime()) / 1000));
            if (seconds < 45) {
                return 'À l\u2019instant';
            }
            const minutes = Math.floor(seconds / 60);
            if (minutes < 60) {
                return 'il y a ' + minutes + ' min';
            }
            const hours = Math.floor(minutes / 60);
            if (hours < 24) {
                return 'il y a ' + hours + ' h';
            }
            const days = Math.floor(hours / 24);
            if (days === 1) {
                return 'hier';
            }
            if (days < 7) {
                return 'il y a ' + days + ' j';
            }
            return date.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' });
        };

        const setBadge = function (count) {
            const n = Math.max(0, parseInt(count, 10) || 0);
            if (badge) {
                badge.textContent = String(n);
                if (n > 0) {
                    badge.removeAttribute('hidden');
                } else {
                    badge.setAttribute('hidden', '');
                }
            }
            if (countLabel) {
                countLabel.textContent = n > 0
                    ? (n + ' non lue' + (n > 1 ? 's' : ''))
                    : 'Tout est à jour';
            }
        };

        const renderEmpty = function () {
            list.innerHTML =
                '<div class="us-notifs-empty">' +
                    '<div class="us-notifs-empty-icon"><i class="fa-regular fa-bell-slash" aria-hidden="true"></i></div>' +
                    '<div class="us-notifs-empty-title">Aucune notification non lue</div>' +
                    '<div class="us-notifs-empty-sub">Tout est à jour, vous pouvez souffler.</div>' +
                '</div>';
        };

        const renderError = function (message) {
            list.innerHTML =
                '<div class="us-notifs-error">' +
                    '<i class="fa-solid fa-circle-exclamation me-2" aria-hidden="true"></i>' +
                    escapeHtml(message || 'Impossible de charger les notifications.') +
                '</div>';
        };

        const renderLoading = function () {
            list.innerHTML =
                '<div class="us-notifs-loading">' +
                    '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>' +
                    '<span>Chargement\u2026</span>' +
                '</div>';
        };

        const renderRows = function (items) {
            if (!Array.isArray(items) || items.length === 0) {
                renderEmpty();
                return;
            }

            const html = items.slice(0, 8).map(function (n) {
                const type = inferType(n.lien);
                const link = n.lien ? String(n.lien) : '';
                const message = escapeHtml(n.message || '');
                const time = escapeHtml(formatTimeAgo(n.cree_le));
                return '' +
                    '<button type="button" class="us-notifs-row" ' +
                        'data-notif-id="' + escapeHtml(n.id) + '" ' +
                        'data-notif-link="' + escapeHtml(link) + '">' +
                        '<span class="us-notifs-icon us-notifs-icon--' + type.key + '" aria-hidden="true">' +
                            '<i class="' + type.icon + '"></i>' +
                        '</span>' +
                        '<span class="us-notifs-row-body">' +
                            '<span class="us-notifs-row-msg">' + message + '</span>' +
                            (time !== '' ? '<span class="us-notifs-row-time">' + time + '</span>' : '') +
                        '</span>' +
                        '<span class="us-notifs-row-dot" aria-hidden="true"></span>' +
                    '</button>';
            }).join('');

            const moreLink = items.length > 8
                ? '<a class="us-notifs-row-more" href="' + escapeHtml(pageUrl) + '">' +
                      'Voir les ' + items.length + ' notifications' +
                  '</a>'
                : '';

            list.innerHTML = html + moreLink;
        };

        const fetchUnread = async function () {
            if (loading || !endpoint) {
                return;
            }
            loading = true;
            renderLoading();
            try {
                const res = await fetch(endpoint, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) {
                    throw new Error('http_' + res.status);
                }
                const data = await res.json();
                const items = Array.isArray(data) ? data : [];
                cache = items;
                loaded = true;
                renderRows(items);
                setBadge(items.length);
            } catch (_err) {
                renderError('Impossible de charger les notifications.');
            } finally {
                loading = false;
            }
        };

        const markRead = async function (id) {
            if (!markEndpoint || !id) {
                return null;
            }
            try {
                const res = await fetch(markEndpoint + '/' + encodeURIComponent(id), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) {
                    return null;
                }
                return await res.json();
            } catch (_err) {
                return null;
            }
        };

        const onRowClick = async function (event) {
            const row = event.target.closest('.us-notifs-row');
            if (!row) {
                return;
            }
            event.preventDefault();

            const id = row.getAttribute('data-notif-id') || '';
            const link = row.getAttribute('data-notif-link') || '';

            row.classList.add('is-dismissing');
            const payload = await markRead(id);

            if (link !== '') {
                window.location.href = link;
                return;
            }

            cache = cache.filter(function (n) {
                return String(n.id) !== String(id);
            });
            renderRows(cache);

            if (payload && typeof payload.unread_count !== 'undefined') {
                setBadge(payload.unread_count);
            } else {
                setBadge(cache.length);
            }
        };

        list.addEventListener('click', function (event) {
            onRowClick(event).catch(function () {
                /* swallow */
            });
        });

        if (markAllForm && markAllEndpoint) {
            markAllForm.addEventListener('submit', function (event) {
                event.preventDefault();
                fetch(markAllEndpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' }
                }).then(function () {
                    cache = [];
                    loaded = true;
                    renderEmpty();
                    setBadge(0);
                }).catch(function () {
                    markAllForm.submit();
                });
            });
        }

        dropdownEl.addEventListener('show.bs.dropdown', function () {
            if (!loaded) {
                fetchUnread();
            }
        });

        dropdownEl.addEventListener('shown.bs.dropdown', function () {
            if (loaded && cache.length === 0) {
                renderEmpty();
            }
        });
    });
})();
