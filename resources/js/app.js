import { createIcons, Apple, ArrowRight, BadgeCheck, Banknote, Bell, BookOpen, Carrot, Check, ChevronDown, ChevronRight, CircleCheck, Citrus, CreditCard, Eye, EyeOff, Filter, Folder, Headset, Home, IndianRupee, LayoutDashboard, LayoutGrid, Leaf, Link, Lock, LogOut, MapPin, Menu, Minus, Package, Pencil, Phone, Plus, RefreshCcw, Salad, Scale, Search, Settings, ShieldCheck, ShoppingBasket, ShoppingCart, Sprout, Star, Store, Trash2, Truck, Upload, User, Users, X } from 'lucide';
import DataTable from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';

const icons = {
    Apple,
    ArrowRight,
    BadgeCheck,
    Banknote,
    Bell,
    BookOpen,
    Carrot,
    Check,
    ChevronDown,
    ChevronRight,
    CircleCheck,
    Citrus,
    CreditCard,
    Eye,
    EyeOff,
    Filter,
    Folder,
    Headset,
    Home,
    IndianRupee,
    LayoutDashboard,
    LayoutGrid,
    Leaf,
    Link,
    Lock,
    LogOut,
    MapPin,
    Menu,
    Minus,
    Package,
    Pencil,
    Phone,
    Plus,
    RefreshCcw,
    Salad,
    Scale,
    Search,
    Settings,
    ShieldCheck,
    ShoppingBasket,
    ShoppingCart,
    Sprout,
    Star,
    Store,
    Trash2,
    Truck,
    Upload,
    User,
    Users,
    X,
};

function renderIcons() {
    createIcons({ icons });
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

    Livewire.hook('morph', () => {
        destroyDataTables();
    });

    Livewire.hook('morphed', () => {
        initDataTables();
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
    });
    Livewire.hook('morph.updated', () => {
        renderIcons();
        initReveals();
    });
});

document.addEventListener('livewire:navigate', () => {
    destroyDataTables();
});

document.addEventListener('livewire:navigated', () => {
    renderIcons();
    initReveals();
    initDataTables();
});
