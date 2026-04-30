(() => {
    const root = document.getElementById('us-front-calendar');
    if (!root) {
        return;
    }

    const rawData = window.uniServeCalendarData || {};
    const feed = Array.isArray(rawData.events) ? rawData.events : [];
    const basePath = window.uniServeBasePath || '';

    const sourceLabels = {
        rendezvous: 'Rendez-vous',
        events_registered: 'Mes événements',
        events_public: 'Événements publics',
    };

    const slotStartHour = 7;
    const slotEndHour = 22;
    const slotMinutes = 30;
    const slotHeight = 22;
    const totalSlots = ((slotEndHour - slotStartHour) * 60) / slotMinutes;
    const totalTimelineMinutes = (slotEndHour - slotStartHour) * 60;

    let activeFilter = 'all';
    let weekOffset = 0;

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#39;');
    }

    function parseDate(value) {
        const date = new Date(value);
        return Number.isNaN(date.getTime()) ? null : date;
    }

    function startOfWeek(date) {
        const copy = new Date(date);
        const day = copy.getDay();
        const diff = day === 0 ? -6 : 1 - day;
        copy.setDate(copy.getDate() + diff);
        copy.setHours(0, 0, 0, 0);
        return copy;
    }

    function addDays(date, days) {
        const copy = new Date(date);
        copy.setDate(copy.getDate() + days);
        return copy;
    }

    function clamp(value, min, max) {
        return Math.min(Math.max(value, min), max);
    }

    function minutesSinceStart(date) {
        return (date.getHours() * 60) + date.getMinutes();
    }

    function formatWeekRange(startDate) {
        const endDate = addDays(startDate, 6);
        const formatter = new Intl.DateTimeFormat('fr-FR', {
            day: '2-digit',
            month: 'short',
        });

        return `${formatter.format(startDate)} → ${formatter.format(endDate)}`;
    }

    function formatDayHeader(date) {
        return new Intl.DateTimeFormat('fr-FR', {
            weekday: 'short',
            day: '2-digit',
            month: 'short',
        }).format(date);
    }

    function formatTime(date) {
        return new Intl.DateTimeFormat('fr-FR', {
            hour: '2-digit',
            minute: '2-digit',
        }).format(date);
    }

    function formatUpcoming(date) {
        return new Intl.DateTimeFormat('fr-FR', {
            day: '2-digit',
            month: 'short',
            hour: '2-digit',
            minute: '2-digit',
        }).format(date);
    }

    function mapEvents() {
        return feed.map((event) => {
            const start = parseDate(event.start);
            const end = parseDate(event.end || event.start);
            return {
                id: event.id,
                title: event.title || 'Élément',
                start,
                end: end || start,
                url: event.url || '',
                color: event.color || '#2f7df4',
                sourceType: event.source_type || 'events_public',
                sourceLabel: sourceLabels[event.source_type] || 'Élément',
                status: event.status || '',
                location: event.location || '',
                ownerLabel: event.owner_label || '',
                isReadonly: !!event.is_readonly,
            };
        }).filter((event) => event.start !== null);
    }

    const mappedEvents = mapEvents();
    const upcomingNode = document.getElementById('us-calendar-upcoming');
    const chips = Array.from(document.querySelectorAll('[data-calendar-filter]'));

    function filteredEvents() {
        if (activeFilter === 'all') {
            return mappedEvents;
        }

        return mappedEvents.filter((event) => event.sourceType === activeFilter);
    }

    function currentWeekStart() {
        const base = new Date();
        base.setHours(0, 0, 0, 0);
        const weekStart = startOfWeek(base);
        weekStart.setDate(weekStart.getDate() + (weekOffset * 7));
        return weekStart;
    }

    function eventWithinWeek(event, weekStart) {
        if (!event.start) {
            return false;
        }

        const weekEnd = addDays(weekStart, 7);
        return event.start >= weekStart && event.start < weekEnd;
    }

    function renderUpcomingList(events) {
        if (!upcomingNode) {
            return;
        }

        const now = new Date();
        const upcoming = events
            .filter((event) => event.start && event.start >= now)
            .sort((a, b) => a.start - b.start)
            .slice(0, 5);

        if (upcoming.length === 0) {
            upcomingNode.innerHTML = '<p class="text-muted small mb-0">Rien à afficher pour le moment.</p>';
            return;
        }

        upcomingNode.innerHTML = upcoming.map((event) => {
            const dateText = formatUpcoming(event.start);
            return `
                <div class="us-upcoming-item">
                    <div class="us-upcoming-dot" style="background:${event.color};"></div>
                    <div>
                        <div class="us-upcoming-title">${escapeHtml(event.title)}</div>
                        <div class="us-upcoming-meta">${escapeHtml(dateText)}${event.ownerLabel ? ' · ' + escapeHtml(event.ownerLabel) : ''}</div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function renderEmptyState(message) {
        root.innerHTML = `
            <div class="us-calendar-empty-state us-calendar-empty-state--agenda">
                <div class="us-calendar-empty-illustration">
                    <i class="bi bi-calendar2-week"></i>
                </div>
                <div>
                    <h3 class="h6 mb-1">${escapeHtml(message)}</h3>
                    <p class="text-muted mb-3">Ajoutez des rendez-vous et événements depuis le backoffice pour remplir cette vue agenda.</p>
                    <div class="d-flex flex-wrap gap-2">
                            <a href="${escapeHtml(basePath + '/rendezvous')}" class="btn btn-primary btn-sm">Voir les rendez-vous</a>
                            <a href="${escapeHtml(basePath + '/evenements')}" class="btn btn-outline-primary btn-sm">Parcourir les événements</a>
                    </div>
                </div>
            </div>
        `;
    }

    function renderCalendar() {
        const weekStart = currentWeekStart();
        const weekEvents = filteredEvents().filter((event) => eventWithinWeek(event, weekStart));

        renderUpcomingList(weekEvents);

        const days = Array.from({ length: 7 }, (_, index) => addDays(weekStart, index));
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const rows = [];
        for (let hour = slotStartHour; hour < slotEndHour; hour += 2) {
            rows.push(hour);
        }

        const timeLabels = rows.map((hour) => `
            <div class="us-agenda-time-cell" style="height:${slotHeight * 2}px;">
                <span>${String(hour).padStart(2, '0')}:00</span>
            </div>
        `).join('');

        const dayColumns = days.map((day) => {
            const dayEvents = weekEvents.filter((event) =>
                event.start.getFullYear() === day.getFullYear()
                && event.start.getMonth() === day.getMonth()
                && event.start.getDate() === day.getDate()
            );

            const isToday = day.getTime() === today.getTime();
            const eventsHtml = dayEvents.map((event) => {
                const startMinutes = clamp(minutesSinceStart(event.start), slotStartHour * 60, slotEndHour * 60 - slotMinutes);
                const endMinutes = clamp(minutesSinceStart(event.end || event.start), slotStartHour * 60 + slotMinutes, slotEndHour * 60);
                const durationMinutes = Math.max(slotMinutes, endMinutes - startMinutes);
                const top = ((startMinutes - (slotStartHour * 60)) / totalTimelineMinutes) * 100;
                const height = (durationMinutes / totalTimelineMinutes) * 100;
                const timeLabel = `${formatTime(event.start)}${event.end ? ' - ' + formatTime(event.end) : ''}`;

                const block = `
                    <a class="us-agenda-event" href="${escapeHtml(event.url || '#')}" style="top:${top}%;height:${height}%;--event-color:${escapeHtml(event.color)};">
                        <div class="us-agenda-event-time">${escapeHtml(timeLabel)}</div>
                        <div class="us-agenda-event-title">${escapeHtml(event.title)}</div>
                    </a>
                `;

                if (event.url) {
                    return block;
                }

                return block.replace('<a', '<div').replace('</a>', '</div>');
            }).join('');

            const nowDate = new Date();
            const showNow = isToday && nowDate.getHours() >= slotStartHour && nowDate.getHours() < slotEndHour;
            const nowMinutes = (nowDate.getHours() * 60) + nowDate.getMinutes();
            const nowTop = showNow ? ((nowMinutes - (slotStartHour * 60)) / totalTimelineMinutes) * 100 : -1;

            return `
                <div class="us-agenda-day${isToday ? ' is-today' : ''}">
                    <div class="us-agenda-day-head">
                        <div class="us-agenda-day-name">${escapeHtml(formatDayHeader(day))}</div>
                        ${isToday ? '<span class="badge text-bg-primary-subtle text-primary-emphasis">Aujourd\'hui</span>' : ''}
                    </div>
                    <div class="us-agenda-day-grid" style="height:${totalSlots * slotHeight}px;">
                        ${Array.from({ length: totalSlots }).map((_, index) => `<div class="us-agenda-slot ${index % 2 === 0 ? 'is-hour' : ''}"></div>`).join('')}
                        ${showNow ? `<div class="us-agenda-now" style="top:${nowTop}%;"></div>` : ''}
                        <div class="us-agenda-events">
                            ${eventsHtml}
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        root.innerHTML = `
            <div class="us-agenda-shell">
                <div class="us-agenda-header">
                    <div>
                        <div class="us-agenda-kicker">Semaine en cours</div>
                        <div class="us-agenda-range">${escapeHtml(formatWeekRange(weekStart))}</div>
                    </div>
                    <div class="us-agenda-controls">
                        <button type="button" class="btn btn-outline-primary btn-sm" data-week-nav="prev">Précédent</button>
                        <button type="button" class="btn btn-primary btn-sm" data-week-nav="today">Aujourd'hui</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-week-nav="next">Suivant</button>
                    </div>
                </div>

                <div class="us-agenda-body">
                    <div class="us-agenda-time-column">
                        <div class="us-agenda-time-head"></div>
                        ${timeLabels}
                    </div>
                    <div class="us-agenda-days">
                        ${dayColumns}
                    </div>
                </div>
            </div>
        `;

        bindWeekNavigation();
        syncChips();
    }

    function syncChips() {
        chips.forEach((chip) => {
            const isActive = chip.getAttribute('data-calendar-filter') === activeFilter;
            chip.classList.toggle('active', isActive);
            chip.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    }

    function bindWeekNavigation() {
        const buttons = Array.from(root.querySelectorAll('[data-week-nav]'));
        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                const nav = button.getAttribute('data-week-nav');
                if (nav === 'prev') {
                    weekOffset -= 1;
                } else if (nav === 'next') {
                    weekOffset += 1;
                } else {
                    weekOffset = 0;
                }
                renderCalendar();
            });
        });
    }

    chips.forEach((chip) => {
        chip.addEventListener('click', () => {
            activeFilter = chip.getAttribute('data-calendar-filter') || 'all';
            renderCalendar();
        });
    });

    renderCalendar();
})();