document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.querySelector('[data-us-login-form]');
    if (loginForm) {
        loginForm.addEventListener('submit', function () {
            loginForm.setAttribute('aria-busy', 'true');
            const btn = loginForm.querySelector('button[type="submit"]');
            if (btn && !btn.disabled) {
                btn.disabled = true;
                btn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Connexion…';
            }
        });
    }

    const chatRoot = document.querySelector('[data-ai-chat]');
    if (chatRoot) {
        const toggleBtn = chatRoot.querySelector('[data-ai-chat-toggle]');
        const closeBtn = chatRoot.querySelector('[data-ai-chat-close]');
        const panel = chatRoot.querySelector('[data-ai-chat-panel]');
        const form = chatRoot.querySelector('[data-ai-chat-form]');
        const input = chatRoot.querySelector('[data-ai-chat-input]');
        const sendBtn = chatRoot.querySelector('[data-ai-chat-send]');
        const messages = chatRoot.querySelector('[data-ai-chat-messages]');
        const endpoint = chatRoot.getAttribute('data-ai-endpoint') || '';
        const history = [];

        const appendMessage = function (text, who) {
            const div = document.createElement('div');
            div.className = 'us-ai-chat-message ' + who;
            div.textContent = text;
            messages.appendChild(div);
            messages.scrollTop = messages.scrollHeight;
        };

        const setOpen = function (open) {
            panel.classList.toggle('d-none', !open);
            if (open) {
                input.focus();
            }
        };

        toggleBtn.addEventListener('click', function () {
            const hidden = panel.classList.contains('d-none');
            setOpen(hidden);
        });

        closeBtn.addEventListener('click', function () {
            setOpen(false);
        });

        const setTyping = function (on) {
            let typing = messages.querySelector('[data-ai-typing]');
            if (on) {
                if (!typing) {
                    typing = document.createElement('div');
                    typing.className = 'us-ai-chat-message bot';
                    typing.setAttribute('data-ai-typing', '1');
                    typing.textContent = '...';
                    messages.appendChild(typing);
                }
                messages.scrollTop = messages.scrollHeight;
                return;
            }
            if (typing) {
                typing.remove();
            }
        };

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const text = (input.value || '').trim();
            if (text === '') {
                return;
            }

            appendMessage(text, 'user');
            history.push({ role: 'user', content: text });
            input.value = '';
            input.disabled = true;
            sendBtn.disabled = true;
            setTyping(true);

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: text, history: history.slice(-6) })
                });

                const payload = await response.json().catch(function () { return {}; });
                if (!response.ok) {
                    appendMessage(payload.error || 'Erreur du service IA.', 'bot');
                } else {
                    const reply = payload.reply || 'Pas de reponse recue.';
                    appendMessage(reply, 'bot');
                    history.push({ role: 'assistant', content: reply });
                }
            } catch (_err) {
                appendMessage('Erreur reseau. Reessayez dans un instant.', 'bot');
            } finally {
                setTyping(false);
                input.disabled = false;
                sendBtn.disabled = false;
                input.focus();
            }
        });
    }

    const mapRoot = document.querySelector('[data-front-map-widget]');
    if (!mapRoot || typeof window.L === 'undefined') {
        return;
    }

    document.querySelectorAll('[data-register-route-form]').forEach(function (form) {
        form.addEventListener('submit', function () {
            const eventId = form.getAttribute('data-event-id') || '';
            if (eventId !== '') {
                sessionStorage.setItem('us_map_route_after_register_' + eventId, '1');
            }
        });
    });

    const mapToggle = mapRoot.querySelector('[data-front-map-toggle]');
    const mapClose = mapRoot.querySelector('[data-front-map-close]');
    const mapBackdrop = mapRoot.querySelector('[data-front-map-backdrop]');
    const mapPanel = mapRoot.querySelector('[data-front-map-panel]');
    const mapCanvas = mapRoot.querySelector('[data-front-map-canvas]');
    const searchInput = mapRoot.querySelector('[data-front-map-search]');
    const searchBtn = mapRoot.querySelector('[data-front-map-search-btn]');
    const locateBtn = mapRoot.querySelector('[data-front-map-locate]');
    const statusEl = mapRoot.querySelector('[data-front-map-status]');

    let map = null;
    let marker = null;
    let userMarker = null;
    let destinationMarker = null;
    let routeLine = null;
    let pickerTargetInput = null;
    let activeDestinationPoint = null;

    const setStatus = function (text) {
        if (statusEl) {
            statusEl.textContent = text;
        }
    };

    const ensureMap = function () {
        if (map !== null) {
            map.invalidateSize();
            return;
        }

        map = window.L.map(mapCanvas).setView([36.8065, 10.1815], 13);
        window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        map.on('click', function (e) {
            const lat = Number(e.latlng.lat || 0).toFixed(5);
            const lng = Number(e.latlng.lng || 0).toFixed(5);

            if (marker === null) {
                marker = window.L.marker(e.latlng).addTo(map);
            } else {
                marker.setLatLng(e.latlng);
            }
            if (pickerTargetInput) {
                pickerTargetInput.value = lat + ', ' + lng;
                setStatus('Lieu choisi depuis la carte : ' + lat + ', ' + lng);
            } else {
                setStatus('Point sélectionné : ' + lat + ', ' + lng);
            }
        });
    };

    const clearRoute = function () {
        if (routeLine !== null && map) {
            map.removeLayer(routeLine);
            routeLine = null;
        }
    };

    const setOpenMap = function (open) {
        if (mapBackdrop) {
            mapBackdrop.classList.toggle('d-none', !open);
        }
        mapPanel.classList.toggle('d-none', !open);
        document.body.classList.toggle('overflow-hidden', open);
        if (open) {
            ensureMap();
            setTimeout(function () { map.invalidateSize(); }, 120);
            if (searchInput) {
                searchInput.focus();
            }
        }
    };

    mapToggle.addEventListener('click', function () {
        const hidden = mapPanel.classList.contains('d-none');
        setOpenMap(hidden);
    });

    mapClose.addEventListener('click', function () {
        setOpenMap(false);
    });

    if (mapBackdrop) {
        mapBackdrop.addEventListener('click', function () {
            setOpenMap(false);
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !mapPanel.classList.contains('d-none')) {
            setOpenMap(false);
        }
    });

    const geocode = async function () {
        const query = (searchInput.value || '').trim();
        if (query === '') {
            return;
        }

        setStatus('Recherche en cours...');
        try {
            const url = 'https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(query);
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (!Array.isArray(data) || data.length === 0) {
                setStatus('Adresse introuvable.');
                return;
            }

            const first = data[0];
            const lat = parseFloat(first.lat);
            const lon = parseFloat(first.lon);
            const point = window.L.latLng(lat, lon);
            map.setView(point, 15);
            if (marker === null) {
                marker = window.L.marker(point).addTo(map);
            } else {
                marker.setLatLng(point);
            }
            if (pickerTargetInput) {
                pickerTargetInput.value = first.display_name || query;
            }
            setStatus(first.display_name || 'Adresse trouvée.');
            return point;
        } catch (_err) {
            setStatus('Erreur de recherche.');
            return null;
        }
    };

    const focusMapOnAddress = function (address) {
        setOpenMap(true);
        const query = (address || '').trim();
        if (query === '') {
            return;
        }
        searchInput.value = query;
        geocode();
    };

    const formatDuration = function (seconds) {
        const totalMinutes = Math.max(1, Math.round(seconds / 60));
        const hours = Math.floor(totalMinutes / 60);
        const minutes = totalMinutes % 60;
        if (hours <= 0) {
            return minutes + ' min';
        }
        return hours + ' h ' + minutes + ' min';
    };

    const drawRoute = async function (fromPoint, toPoint) {
        const url = 'https://router.project-osrm.org/route/v1/driving/'
            + fromPoint.lng + ',' + fromPoint.lat + ';' + toPoint.lng + ',' + toPoint.lat
            + '?overview=full&geometries=geojson';

        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        if (!data || !Array.isArray(data.routes) || data.routes.length === 0) {
            throw new Error('Route not found');
        }

        const route = data.routes[0];
        const coordinates = route.geometry && Array.isArray(route.geometry.coordinates) ? route.geometry.coordinates : [];
        if (coordinates.length === 0) {
            throw new Error('Empty geometry');
        }

        const latLngs = coordinates.map(function (coord) {
            return [coord[1], coord[0]];
        });

        clearRoute();
        routeLine = window.L.polyline(latLngs, {
            color: '#2563eb',
            weight: 5,
            opacity: 0.9
        }).addTo(map);

        map.fitBounds(routeLine.getBounds(), { padding: [24, 24] });

        const km = (route.distance || 0) / 1000;
        const eta = formatDuration(route.duration || 0);
        setStatus('Itinéraire tracé (bleu) : ' + km.toFixed(1) + ' km, environ ' + eta + '.');
    };

    const startRouteFlow = async function (address) {
        setOpenMap(true);
        ensureMap();
        const query = (address || '').trim();
        if (query === '') {
            setStatus('Lieu événement manquant.');
            return;
        }

        searchInput.value = query;
        setStatus('Recherche du lieu événement...');
        const destinationPoint = await geocode();
        if (!destinationPoint) {
            setStatus('Lieu événement introuvable.');
            return;
        }

        activeDestinationPoint = destinationPoint;
        if (destinationMarker === null) {
            destinationMarker = window.L.marker(destinationPoint).addTo(map);
        } else {
            destinationMarker.setLatLng(destinationPoint);
        }

        setStatus('Autorisez « Ma position » pour tracer votre itinéraire.');
    };

    searchBtn.addEventListener('click', function () {
        geocode();
    });

    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            geocode();
        }
    });

    locateBtn.addEventListener('click', function () {
        if (!navigator.geolocation) {
            setStatus('Géolocalisation non supportée.');
            return;
        }

        setStatus('Localisation en cours...');
        navigator.geolocation.getCurrentPosition(function (pos) {
            const point = window.L.latLng(pos.coords.latitude, pos.coords.longitude);
            map.setView(point, 16);
            if (userMarker === null) {
                userMarker = window.L.marker(point).addTo(map);
            } else {
                userMarker.setLatLng(point);
            }

            if (activeDestinationPoint !== null) {
                drawRoute(point, activeDestinationPoint).catch(function () {
                    setStatus('Impossible de calculer le trajet pour le moment.');
                });
            } else {
                setStatus('Position actuelle détectée.');
            }
        }, function () {
            setStatus('Impossible de récupérer votre position.');
        }, { enableHighAccuracy: true, timeout: 10000 });
    });

    document.querySelectorAll('[data-map-focus-btn]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const address = btn.getAttribute('data-map-address') || '';
            pickerTargetInput = null;
            activeDestinationPoint = null;
            clearRoute();
            focusMapOnAddress(address);
        });
    });

    document.querySelectorAll('[data-map-picker-btn]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetInputId = btn.getAttribute('data-map-target-input') || '';
            pickerTargetInput = targetInputId ? document.getElementById(targetInputId) : null;
            activeDestinationPoint = null;
            clearRoute();
            setOpenMap(true);
            setStatus('Cliquez sur la carte pour choisir le lieu.');
        });
    });

    document.querySelectorAll('[data-map-route-btn]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            pickerTargetInput = null;
            const address = btn.getAttribute('data-map-address') || '';
            startRouteFlow(address).catch(function () {
                setStatus('Impossible de lancer l’itinéraire.');
            });
        });
    });

    const autoRoute = document.querySelector('[data-map-auto-route]');
    if (autoRoute) {
        pickerTargetInput = null;
        const address = autoRoute.getAttribute('data-map-address') || '';
        startRouteFlow(address).catch(function () {
            setStatus('Impossible de lancer l’itinéraire.');
        });
        return;
    }

    const routeContext = document.querySelector('[data-map-route-context]');
    if (routeContext) {
        const eventId = routeContext.getAttribute('data-map-event-id') || '';
        const isRegistered = routeContext.getAttribute('data-map-is-registered') === '1';
        const address = routeContext.getAttribute('data-map-address') || '';
        const storageKey = 'us_map_route_after_register_' + eventId;
        const shouldOpenFromSubmit = eventId !== '' && sessionStorage.getItem(storageKey) === '1';

        if (shouldOpenFromSubmit && isRegistered) {
            sessionStorage.removeItem(storageKey);
            pickerTargetInput = null;
            startRouteFlow(address).catch(function () {
                setStatus('Impossible de lancer l’itinéraire.');
            });
        }
    }
});
