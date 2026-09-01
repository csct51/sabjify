import { createIcons, Apple, ArrowLeft, ArrowRight, BadgeCheck, Banknote, Bell, BookOpen, Carrot,   Check, ChefHat, ChevronDown, ChevronLeft, ChevronRight, CircleCheck, Citrus, CreditCard, Crosshair, Eye, EyeOff, FileText, Filter, Folder, Gift, Headset, Home, IndianRupee, KeyRound, LayoutDashboard, LayoutGrid, Leaf, Link, ListChecks, Locate, Lock, LogOut, MapPin, Menu, Minus, Moon, Navigation, Package, Pencil, Phone, Plus, RefreshCcw, Salad, Scale, Search, Settings, Shield, ShieldCheck, ShoppingBasket, ShoppingCart, Sprout, Star, Store, Sun, Trash2, Truck, Upload, User, UserCircle, Users, Utensils, X } from 'lucide';
import DataTable from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const icons = {
    Apple,
    ArrowLeft,
    ArrowRight,
    BadgeCheck,
    Banknote,
    Bell,
    BookOpen,
    Carrot,
    Check,
    ChefHat,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    CircleCheck,
    Citrus,
    CreditCard,
    Crosshair,
    Eye,
    EyeOff,
    FileText,
    Filter,
    Folder,
    Gift,
    Headset,
    Home,
    IndianRupee,
    KeyRound,
    LayoutDashboard,
    LayoutGrid,
    Leaf,
    ListChecks,
    Link,
    Locate,
    Lock,
    LogOut,
    MapPin,
    Menu,
    Minus,
    Navigation,
    Package,
    Pencil,
    Phone,
    Plus,
    RefreshCcw,
    Salad,
    Scale,
    Search,
    Settings,
    Shield,
    ShieldCheck,
    ShoppingBasket,
    ShoppingCart,
    Sprout,
    Star,
    Store,
    Sun,
    Trash2,
    Truck,
    Upload,
    User,
    UserCircle,
    Users,
    Utensils,
    X,
    Moon,
};

function renderIcons() {
    createIcons({ icons });
}

let pageLoadingBar;

function initPageLoadingBar() {
    if (pageLoadingBar) {
        return;
    }

    pageLoadingBar = document.createElement('div');
    pageLoadingBar.className = 'page-loading-bar';
    pageLoadingBar.innerHTML = '<div class="page-loading-bar-inner"></div>';
    document.body.appendChild(pageLoadingBar);
}

function startPageLoading() {
    initPageLoadingBar();
    pageLoadingBar.classList.add('active');
}

function stopPageLoading() {
    if (! pageLoadingBar) {
        return;
    }

    pageLoadingBar.classList.remove('active');
}

function animatePageEnter() {
    const main = document.querySelector('main') ?? document.body;

    main.classList.remove('page-enter');
    void main.offsetWidth;
    main.classList.add('page-enter');

    main.addEventListener('animationend', (event) => {
        if (event.target === main) {
            main.classList.remove('page-enter');
        }
    }, { once: true });
}

let revealObserver;

function initReveals() {
    if (! revealObserver) {
        revealObserver = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        revealObserver.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.1, rootMargin: '0px 0px -40px 0px' }
        );
    }

    document.querySelectorAll('[data-reveal]:not(.is-visible)').forEach((el) => revealObserver.observe(el));
}

function findFieldForError(key) {
    const fields = document.querySelectorAll('[wire\\:model], [wire\\:model\\.live], [wire\\:model\\.defer], [wire\\:model\\.blur], [wire\\:model\\.lazy]');

    for (const field of fields) {
        const modelNames = Array.from(field.attributes)
            .map((attr) => attr.name)
            .filter((name) => name.startsWith('wire:model'));

        if (modelNames.some((name) => field.getAttribute(name) === key)) {
            return field;
        }
    }

    return null;
}

const activeDataTables = new WeakMap();

function initDataTables() {
    document.querySelectorAll('table[data-datatable]').forEach((table) => {
        if (DataTable.isDataTable(table) || activeDataTables.has(table)) {
            return;
        }

        const dt = new DataTable(table, {
            pageLength: 10,
            lengthChange: true,
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'All']],
            searching: true,
            ordering: true,
            info: true,
            stateSave: true,
            columnDefs: [
                {
                    targets: 0,
                    searchable: false,
                    className: 'px-4 py-3 text-stone-400',
                    render: (data, type, row, meta) => (type === 'sort' || type === 'display' ? meta.row + 1 : data),
                },
            ],
        });

        activeDataTables.set(table, dt);
    });
}

function destroyDataTables() {
    document.querySelectorAll('table[data-datatable]').forEach((table) => {
        const dt = activeDataTables.get(table);

        if (dt) {
            dt.destroy();
            activeDataTables.delete(table);
        }
    });
}

const activeMaps = new WeakMap();

const mapPinIcon = L.divIcon({
    className: '',
    html: '<span class="flex items-center justify-center w-10 h-10 -ml-5 -mt-10 rounded-full bg-brand-600 text-white shadow-lg ring-4 ring-white"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg></span>',
    iconSize: [0, 0],
    iconAnchor: [0, 0],
});

const mapPinIconRed = L.divIcon({
    className: '',
    html: '<span class="flex items-center justify-center w-10 h-10 -ml-5 -mt-10 rounded-full bg-red-600 text-white shadow-lg ring-4 ring-white"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg></span>',
    iconSize: [0, 0],
    iconAnchor: [0, 0],
});

const mapStoreIcon = L.divIcon({
    className: '',
    html: '<span class="flex items-center justify-center w-10 h-10 -ml-5 -mt-10 rounded-full bg-stone-900 text-white shadow-lg ring-4 ring-white"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/></svg></span>',
    iconSize: [0, 0],
    iconAnchor: [0, 0],
});

function mapComponentWire(element) {
    const root = element.closest('[wire\\:id]');

    if (! root) {
        return null;
    }

    return Livewire.find(root.getAttribute('wire:id')) ?? null;
}

function distanceKm(aLat, aLng, bLat, bLng) {
    const earthRadiusKm = 6371;
    const toRadians = (degrees) => (degrees * Math.PI) / 180;
    const dLat = toRadians(bLat - aLat);
    const dLng = toRadians(bLng - aLng);
    const a = Math.sin(dLat / 2) ** 2 + Math.cos(toRadians(aLat)) * Math.cos(toRadians(bLat)) * Math.sin(dLng / 2) ** 2;

    return earthRadiusKm * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function initMaps() {
    document.querySelectorAll('[data-leaflet-map]').forEach((element) => {
        try {
            initializeMap(element);
        } catch (error) {
            // Never let map initialization errors break Livewire's morph/event pipeline.
        }
    });
}

function initializeMap(element) {
    if (activeMaps.has(element) || element._leaflet_id) {
        return;
    }

    let config;

    try {
        config = JSON.parse(element.dataset.config);
    } catch (error) {
        config = { lat: 21.2514, lng: 81.6296, zoom: 11, radius: null, pin: false, autofill: false };
    }

    const map = L.map(element, { scrollWheelZoom: true });
    map.setView([config.lat, config.lng], config.zoom ?? 13);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map);

    if (config.autofill && Array.isArray(config.deliveryAreas)) {
        config.deliveryAreas.forEach((area) => {
            L.circle([area.lat, area.lng], {
                radius: area.radiusKm * 1000,
                color: '#16a34a',
                weight: 1.5,
                dashArray: '6 6',
                fillColor: '#22c55e',
                fillOpacity: 0.1,
                interactive: false,
            }).addTo(map);
        });
    }

    if (Array.isArray(config.existingAreas) && config.existingAreas.length > 0) {
        config.existingAreas.forEach((area) => {
            const circle = L.circle([area.lat, area.lng], {
                radius: area.radiusKm * 1000,
                color: '#d97706',
                weight: 1.5,
                dashArray: '6 6',
                fillColor: '#f59e0b',
                fillOpacity: 0.15,
                interactive: false,
            }).addTo(map);

            if (area.name) {
                circle.bindTooltip(area.name, { permanent: true, direction: 'center', className: 'delivery-zone-tooltip' }).openTooltip();
            }
        });
    }

    if (config.readonly) {
        if (config.pin) {
            L.marker([config.lat, config.lng], { icon: mapPinIcon, interactive: false }).addTo(map);
        }

        const routes = Array.isArray(config.routes) ? config.routes : [];

        if (routes.length > 0) {
            const bounds = config.pin ? [[config.lat, config.lng]] : [];

            routes.forEach((route) => {
                const [fromLat, fromLng] = route.from;
                const [toLat, toLng] = route.to;

                L.marker([fromLat, fromLng], { icon: mapStoreIcon, interactive: false }).addTo(map);
                bounds.push([fromLat, fromLng]);

                if (route.radiusKm > 0) {
                    L.circle([fromLat, fromLng], {
                        radius: route.radiusKm * 1000,
                        color: '#d97706',
                        weight: 1.5,
                        dashArray: '6 6',
                        fillColor: '#f59e0b',
                        fillOpacity: 0.15,
                        interactive: false,
                    }).addTo(map);
                }

                const drawFallback = () => {
                    L.polyline([[fromLat, fromLng], [toLat, toLng]], {
                        color: '#0f766e',
                        weight: 2,
                        dashArray: '6 6',
                        interactive: false,
                    }).addTo(map);
                };

                fetch(`https://router.project-osrm.org/route/v1/driving/${fromLng},${fromLat};${toLng},${toLat}?overview=full&geometries=geojson`)
                    .then((response) => response.json())
                    .then((data) => {
                        const coordinates = data?.routes?.[0]?.geometry?.coordinates;

                        if (data?.code !== 'Ok' || ! Array.isArray(coordinates)) {
                            drawFallback();

                            return;
                        }

                        const latlngs = coordinates.map(([lng, lat]) => [lat, lng]);

                        L.polyline(latlngs, { color: '#0f766e', weight: 4, interactive: false }).addTo(map);

                        map.fitBounds([...bounds, ...latlngs], { padding: [24, 24] });
                    })
                    .catch(drawFallback);
            });

            map.fitBounds(bounds, { padding: [24, 24] });
        }

        return;
    }

    let currentLatLng = null;
    let marker = null;
    let circle = null;
    let statusEl = null;
    let geocodeTimer = null;

    const deliveryStatusIcons = {
        ok: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>',
        no: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>',
    };

    const setDeliveryStatus = (deliverable) => {
        if (! config.autofill) {
            return;
        }

        if (! statusEl) {
            statusEl = document.createElement('div');
            statusEl.setAttribute('role', 'status');
            statusEl.style.cssText = 'position:absolute;top:10px;left:50%;transform:translateX(-50%);z-index:1000;display:none;align-items:center;gap:6px;padding:7px 12px;border-radius:9999px;font-size:12px;font-weight:600;box-shadow:0 2px 8px rgba(0,0,0,.12);white-space:nowrap;';
            element.appendChild(statusEl);
        }

        if (deliverable === null || deliverable === undefined) {
            statusEl.style.display = 'none';

            return;
        }

        if (marker) {
            marker.setIcon(deliverable ? mapPinIcon : mapPinIconRed);
        }

        if (deliverable) {
            statusEl.innerHTML = deliveryStatusIcons.ok + ' <span>Delivers to this location</span>';
            statusEl.style.background = '#d1fae5';
            statusEl.style.color = '#065f46';
        } else {
            statusEl.innerHTML = deliveryStatusIcons.no + ' <span>Outside delivery area</span>';
            statusEl.style.background = '#fee2e2';
            statusEl.style.color = '#991b1b';
        }

        statusEl.style.display = 'inline-flex';
    };

    const deliveryAreas = Array.isArray(config.deliveryAreas) ? config.deliveryAreas : [];

    const isDeliverable = (lat, lng) => {
        if (deliveryAreas.length === 0) {
            return true;
        }

        return deliveryAreas.some((area) => distanceKm(lat, lng, area.lat, area.lng) <= area.radiusKm);
    };

    const emit = () => {
        const detail = {
            lat: currentLatLng.lat,
            lng: currentLatLng.lng,
            radiusKm: config.radius ? (circle ? circle.getRadius() / 1000 : config.radius) : null,
        };

        setDeliveryStatus(isDeliverable(detail.lat, detail.lng));

        element.dispatchEvent(new CustomEvent('location:update', { detail }));

        const wire = mapComponentWire(element);

        if (wire) {
            if (config.autofill) {
                window.clearTimeout(geocodeTimer);
                geocodeTimer = window.setTimeout(() => wire.reverseGeocode(detail.lat, detail.lng), 500);
            } else {
                wire.updateLocation(detail.lat, detail.lng, detail.radiusKm ?? null);
            }
        }
    };

    const placeMarker = (latlng) => {
        currentLatLng = L.latLng(latlng);

        if (! marker) {
            marker = L.marker(currentLatLng, { icon: mapPinIcon, draggable: false }).addTo(map);
        } else {
            marker.setLatLng(currentLatLng);
        }

        if (config.radius) {
            if (! circle) {
                circle = L.circle(currentLatLng, { radius: config.radius * 1000 }).addTo(map);
            } else {
                circle.setLatLng(currentLatLng);
            }
        }
    };

    if (config.pin) {
        placeMarker([config.lat, config.lng]);

        if (config.autofill) {
            setDeliveryStatus(isDeliverable(config.lat, config.lng));
        }
    }

    if (config.radius && config.pin) {
        circle = L.circle([config.lat, config.lng], { radius: config.radius * 1000 }).addTo(map);
    }

    if (config.geolocate) {
        const requestCurrentLocation = (onError) => {
            if (! navigator.geolocation) {
                return;
            }

            navigator.geolocation.getCurrentPosition((position) => {
                const latlng = [position.coords.latitude, position.coords.longitude];
                placeMarker(latlng);
                map.setView(latlng, 15);
                emit();
                window.dispatchEvent(new CustomEvent('geolocation:permission-granted'));
            }, onError, { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 });
        };

        const locateButton = L.control({ position: 'topleft' });

        locateButton.onAdd = () => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'leaflet-bar leaflet-control leaflet-control-custom flex items-center justify-center w-9 h-9 bg-white text-stone-700 border-b';
            button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>';
            button.title = 'Use my location';
            button.setAttribute('aria-label', 'Use my location');

            button.addEventListener('click', () => {
                button.disabled = true;
                requestCurrentLocation((error) => {
                    button.disabled = false;
                    if (error && error.code === 1) {
                        window.dispatchEvent(new CustomEvent('geolocation:permission-denied'));
                    }
                });
            });

            return button;
        };

        locateButton.addTo(map);

        element.addEventListener('locate:request', () => {
            requestCurrentLocation((error) => {
                if (error && error.code === 1) {
                    window.dispatchEvent(new CustomEvent('geolocation:permission-denied'));
                }
            });
        });
    }

    map.on('click', (event) => {
        placeMarker(event.latlng);
        emit();
    });

    element.addEventListener('radius:update', (event) => {
        const radiusKm = Number(event.detail.radiusKm);

        if (circle && Number.isFinite(radiusKm) && radiusKm > 0) {
            circle.setRadius(radiusKm * 1000);
        }

        const wire = mapComponentWire(element);

        if (wire && Number.isFinite(radiusKm) && radiusKm > 0) {
            wire.updateRadius(radiusKm);
        }
    });

    requestAnimationFrame(() => map.invalidateSize());

    activeMaps.set(element, map);
}

function destroyMap(element) {
    const map = activeMaps.get(element);

    if (map) {
        map.remove();
        activeMaps.delete(element);
    }

    delete element._leaflet_id;
}

function destroyMaps() {
    document.querySelectorAll('[data-leaflet-map]').forEach(destroyMap);
}

function observeMaps() {
    const observer = new MutationObserver((mutations) => {
        for (const mutation of mutations) {
            for (const node of mutation.addedNodes) {
                if (node.nodeType !== 1) {
                    continue;
                }

                if (node.matches?.('[data-leaflet-map]')) {
                    initMaps();
                } else if (node.querySelectorAll?.('[data-leaflet-map]').length) {
                    initMaps();
                }
            }

            for (const node of mutation.removedNodes) {
                if (node.nodeType !== 1) {
                    continue;
                }

                if (node.matches?.('[data-leaflet-map]')) {
                    destroyMap(node);
                } else {
                    node.querySelectorAll?.('[data-leaflet-map]').forEach(destroyMap);
                }
            }
        }
    });

    observer.observe(document.body, { childList: true, subtree: true });
}

function focusFirstInvalidField(component) {
    const errors = component?.snapshot?.memo?.errors ?? {};

    if (Object.keys(errors).length === 0) {
        return;
    }

    const field = findFieldForError(Object.keys(errors)[0]);

    if (field) {
        field.focus({ preventScroll: true });
        field.scrollIntoView?.({ behavior: 'smooth', block: 'center' });
    }
}

document.addEventListener('livewire:init', () => {
    renderIcons();
    initReveals();
    initDataTables();
    initMaps();
    observeMaps();
    animatePageEnter();

    Livewire.hook('morph', () => {
        destroyDataTables();
    });

    Livewire.hook('morphed', () => {
        initDataTables();
        initMaps();
    });

    Livewire.interceptMessage(({ message, onFinish }) => {
        const hasUserAction = Array.from(message.actions).some((action) => ! action.name.startsWith('$'));

        if (! hasUserAction) {
            return;
        }

        const component = message.component;

        onFinish(() => {
            focusFirstInvalidField(component);
        });
    });

    Livewire.hook('morph.added', () => {
        renderIcons();
        initReveals();
        initMaps();
    });
    Livewire.hook('morph.updated', () => {
        renderIcons();
        initReveals();
        initMaps();
    });
});

document.addEventListener('livewire:navigate', () => {
    destroyDataTables();
    destroyMaps();
});

document.addEventListener('livewire:navigating', () => {
    startPageLoading();
});

document.addEventListener('livewire:navigated', () => {
    stopPageLoading();
    animatePageEnter();
    renderIcons();
    initReveals();
    initDataTables();
    initMaps();
});
