(() => {
    const root = document.getElementById('us-front-calendar');
    if (!root) {
        return;
    }

    const rawData = window.uniServeCalendarData || {};
    const feed = Array.isArray(rawData.events) ? rawData.events : [];
    const basePath = window.uniServeBasePath || '';
    const aiBriefEndpoint = window.uniServeAiBriefEndpoint || '';

    const sourceLabels = {
        rendezvous: 'Rendez-vous',
        events_registered: 'Mes événements',
        events_public: 'Événements publics',
    };

    const slotStartHour = 7;
    const slotEndHour = 22;
    const slotMinutes = 30;
    const slotHeight = 24;
    const totalSlots = ((slotEndHour - slotStartHour) * 60) / slotMinutes;
    const totalTimelineMinutes = (slotEndHour - slotStartHour) * 60;

    let activeFilter = 'all';
    let weekOffset = 0;
    let aiBriefData = normalizeBrief(window.uniServeAiBriefData || {});

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#39;');
    }

    function absoluteAppPath(path) {
        const p = String(path ?? '').trim();
        const base = String(basePath || '').replace(/\/$/, '');
        if (p === '' || p === '#') {
            return base !== '' ? `${base}/` : '/';
        }
        if (/^https?:\/\//i.test(p)) {
            return p;
        }
        const normalized = p.startsWith('/') ? p : `/${p}`;
        // Server may already emit URLs with the app base (e.g. /INTEG/evenements/...); avoid /INTEG/INTEG/...
        if (base !== '' && (normalized === base || normalized.startsWith(`${base}/`))) {
            return normalized;
        }
        return `${base}${normalized}`;
    }

    function fallbackPathForSource(sourceType) {
        if (sourceType === 'rendezvous') {
            return '/rendezvous';
        }
        if (sourceType === 'events_registered' || sourceType === 'events_public') {
            return '/evenements';
        }
        return '/';
    }

    function resolveEventHref(event) {
        const raw = String(event.url ?? '').trim();
        if (raw !== '') {
            return absoluteAppPath(raw);
        }
        return absoluteAppPath(fallbackPathForSource(event.sourceType));
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

    function normalizeBrief(payload) {
        const source = String(payload.source || 'fallback').toLowerCase() === 'ai' ? 'ai' : 'fallback';
        const normalizePriority = (item) => ({
            label: String(item?.label || '').trim(),
            score: Number.isFinite(Number(item?.score)) ? Math.max(0, Math.min(100, Number(item.score))) : 0,
            reason: String(item?.reason || '').trim(),
            start: String(item?.start || '').trim(),
            source_type: String(item?.source_type || '').trim(),
        });
        const normalizeAction = (item) => {
            const impactRaw = String(item?.impact || 'medium').toLowerCase();
            const impact = ['low', 'medium', 'high'].includes(impactRaw) ? impactRaw : 'medium';
            return {
                action: String(item?.action || '').trim(),
                suggested_time: String(item?.suggested_time || '').trim(),
                impact,
            };
        };
        const normalizeDaily = (item) => ({
            day: String(item?.day || '').trim(),
            brief: String(item?.brief || '').trim(),
        });

        return {
            summary: String(payload.summary || '').trim(),
            ranked_priorities: Array.isArray(payload.ranked_priorities) ? payload.ranked_priorities.map(normalizePriority).filter((item) => item.label !== '').slice(0, 3) : [],
            risks: Array.isArray(payload.risks) ? payload.risks.map((item) => String(item || '').trim()).filter((item) => item !== '').slice(0, 3) : [],
            next_actions: Array.isArray(payload.next_actions) ? payload.next_actions.map(normalizeAction).filter((item) => item.action !== '').slice(0, 3) : [],
            daily_briefs: Array.isArray(payload.daily_briefs) ? payload.daily_briefs.map(normalizeDaily).filter((item) => item.day !== '' && item.brief !== '').slice(0, 7) : [],
            source,
            generated_at: String(payload.generated_at || '').trim(),
        };
    }

    function formatGeneratedAt(value) {
        const date = parseDate(value);
        if (!date) {
            return '--';
        }
        return new Intl.DateTimeFormat('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
        }).format(date);
    }

    function renderBriefList(node, rows, fallbackText, renderRow) {
        if (!node) {
            return;
        }
        if (!Array.isArray(rows) || rows.length === 0) {
            node.innerHTML = `<li class="us-ai-brief-muted">${escapeHtml(fallbackText)}</li>`;
            return;
        }
        node.innerHTML = rows.map(renderRow).join('');
    }

    function renderAiBrief() {
        const sourceNode = document.getElementById('us-ai-brief-source');
        const summaryNode = document.getElementById('us-ai-brief-summary');
        const freshnessNode = document.getElementById('us-ai-freshness');
        const prioritiesNode = document.getElementById('us-ai-priorities');
        const risksNode = document.getElementById('us-ai-risks');
        const actionsNode = document.getElementById('us-ai-actions');
        const modalSummaryNode = document.getElementById('us-ai-modal-summary');
        const modalPrioritiesNode = document.getElementById('us-ai-modal-priorities');
        const modalActionsNode = document.getElementById('us-ai-modal-actions');
        const modalDailyNode = document.getElementById('us-ai-modal-daily');
        const errorNode = document.getElementById('us-ai-brief-error');

        if (sourceNode) {
            sourceNode.textContent = aiBriefData.source === 'ai' ? 'Analyse enrichie' : 'Vue locale';
            sourceNode.classList.remove('text-bg-primary', 'text-bg-secondary');
            sourceNode.classList.add(aiBriefData.source === 'ai' ? 'text-bg-primary' : 'text-bg-secondary');
        }

        const summary = aiBriefData.summary !== '' ? aiBriefData.summary : 'Résumé dérivé de votre agenda et des filtres sélectionnés.';
        if (summaryNode) {
            summaryNode.textContent = summary;
        }
        if (modalSummaryNode) {
            modalSummaryNode.textContent = summary;
        }

        if (freshnessNode) {
            freshnessNode.textContent = `Mis à jour: ${formatGeneratedAt(aiBriefData.generated_at)}`;
        }

        if (errorNode) {
            errorNode.textContent = '';
            errorNode.classList.add('d-none');
        }

        renderBriefList(
            prioritiesNode,
            aiBriefData.ranked_priorities,
            'Rien à signaler.',
            (item) => `<li><span class="fw-semibold">${escapeHtml(item.label)}</span><small>${escapeHtml(item.reason || '')}</small></li>`
        );
        renderBriefList(
            modalPrioritiesNode,
            aiBriefData.ranked_priorities,
            'Rien à signaler.',
            (item) => `<li><span class="fw-semibold">${escapeHtml(item.label)}</span><small>${escapeHtml(item.reason || '')}</small></li>`
        );

        renderBriefList(
            risksNode,
            aiBriefData.risks,
            'Rien à signaler.',
            (item) => `<li>${escapeHtml(item)}</li>`
        );

        const renderAction = (item) => `<li><span>${escapeHtml(item.action)}</span><small>${escapeHtml(item.suggested_time || '')}</small></li>`;
        renderBriefList(actionsNode, aiBriefData.next_actions, 'Rien à signaler.', renderAction);
        renderBriefList(modalActionsNode, aiBriefData.next_actions, 'Rien à signaler.', renderAction);

        renderBriefList(
            modalDailyNode,
            aiBriefData.daily_briefs,
            'Aucun indicateur pour ces jours.',
            (item) => `<li><span class="fw-semibold">${escapeHtml(item.day)}</span><small>${escapeHtml(item.brief)}</small></li>`
        );
    }

    function setBriefLoading(loading) {
        const refreshButtons = [
            document.getElementById('us-ai-brief-refresh'),
            document.getElementById('us-ai-brief-refresh-modal'),
        ];

        refreshButtons.forEach((button) => {
            if (!button) {
                return;
            }
            button.disabled = loading;
            button.textContent = loading ? 'Mise à jour…' : 'Actualiser';
        });
    }

    function showBriefError(message) {
        const errorNode = document.getElementById('us-ai-brief-error');
        if (!errorNode) {
            return;
        }
        errorNode.textContent = message;
        errorNode.classList.remove('d-none');
    }

    async function refreshAiBrief(forceRegeneration) {
        if (!aiBriefEndpoint) {
            return;
        }

        const params = new URLSearchParams({
            week_offset: String(weekOffset),
            filter: activeFilter,
        });
        if (forceRegeneration) {
            params.set('refresh', '1');
        }

        setBriefLoading(true);
        try {
            const response = await fetch(`${aiBriefEndpoint}?${params.toString()}`, {
                headers: {
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('La mise à jour du brief IA a échoué.');
            }

            const payload = await response.json();
            aiBriefData = normalizeBrief(payload || {});
            renderAiBrief();
        } catch (error) {
            showBriefError('Impossible de rafraîchir le brief IA pour le moment.');
        } finally {
            setBriefLoading(false);
        }
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
    const upcomingCountNode = document.getElementById('us-calendar-upcoming-count');
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

    function renderUpcomingList() {
        if (!upcomingNode) {
            return;
        }

        const now = new Date();
        const upcoming = filteredEvents()
            .filter((event) => event.start && event.start >= now)
            .sort((a, b) => a.start - b.start)
            .slice(0, 5);

        if (upcomingCountNode) {
            upcomingCountNode.textContent = String(upcoming.length);
        }

        if (upcoming.length === 0) {
            upcomingNode.innerHTML = '<p class="text-muted small mb-0">Rien à afficher pour le moment.</p>';
            return;
        }

        upcomingNode.innerHTML = upcoming.map((event) => {
            const dateText = formatUpcoming(event.start);
            const href = escapeHtml(resolveEventHref(event));
            return `
                <a class="us-upcoming-item text-reset text-decoration-none" href="${href}">
                    <div class="us-upcoming-dot" style="background:${event.color};"></div>
                    <div>
                        <div class="us-upcoming-title">${escapeHtml(event.title)}</div>
                        <div class="us-upcoming-meta">${escapeHtml(dateText)}${event.ownerLabel ? ' · ' + escapeHtml(event.ownerLabel) : ''}</div>
                    </div>
                </a>
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
                            <a href="${escapeHtml(absoluteAppPath('/rendezvous'))}" class="btn btn-primary btn-sm">Voir les rendez-vous</a>
                            <a href="${escapeHtml(absoluteAppPath('/evenements'))}" class="btn btn-outline-primary btn-sm">Parcourir les événements</a>
                    </div>
                </div>
            </div>
        `;
    }

    function renderCalendar() {
        const weekStart = currentWeekStart();
        const weekEvents = filteredEvents().filter((event) => eventWithinWeek(event, weekStart));

        renderUpcomingList();

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
                const detailLabel = `${String(event.title ?? '')} — ${timeLabel}`;
                const detailAttr = escapeHtml(detailLabel);
                const typeSlug = String(event.sourceType || 'other').replace(/[^a-z0-9_-]/gi, '');
                const innerHtml = `<div class="us-agenda-event-time">${escapeHtml(timeLabel)}</div><div class="us-agenda-event-title">${escapeHtml(event.title)}</div>`;
                const styleAttr = `style="top:${top}%;height:${height}%;--event-color:${escapeHtml(event.color)};"`;
                const typeClass = typeSlug !== '' ? ` us-agenda-event--${typeSlug}` : '';
                const href = escapeHtml(resolveEventHref(event));
                return `<a class="us-agenda-event${typeClass}" href="${href}" data-source-type="${escapeHtml(event.sourceType || '')}" title="${detailAttr}" aria-label="${detailAttr}" ${styleAttr}>${innerHtml}</a>`;
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
                        <div class="us-agenda-kicker">Période</div>
                        <div class="us-agenda-range">${escapeHtml(formatWeekRange(weekStart))}</div>
                    </div>
                    <div class="us-agenda-controls">
                        <button type="button" class="btn btn-outline-primary btn-sm" data-week-nav="prev">Précédent</button>
                        <button type="button" class="btn btn-primary btn-sm" data-week-nav="today">Aujourd'hui</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-week-nav="next">Suivant</button>
                    </div>
                </div>

                <div class="us-agenda-body-wrap">
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
                refreshAiBrief(false);
            });
        });
    }

    chips.forEach((chip) => {
        chip.addEventListener('click', () => {
            activeFilter = chip.getAttribute('data-calendar-filter') || 'all';
            renderCalendar();
            refreshAiBrief(false);
        });
    });

    const refreshButton = document.getElementById('us-ai-brief-refresh');
    if (refreshButton) {
        refreshButton.addEventListener('click', () => {
            refreshAiBrief(true);
        });
    }
    const refreshModalButton = document.getElementById('us-ai-brief-refresh-modal');
    if (refreshModalButton) {
        refreshModalButton.addEventListener('click', () => {
            refreshAiBrief(true);
        });
    }

    renderCalendar();
    renderAiBrief();
})();