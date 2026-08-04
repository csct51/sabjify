import { createIcons, Apple, ArrowRight, BadgeCheck, Banknote, Carrot, Check, ChevronDown, ChevronRight, CircleCheck, Citrus, CreditCard, Folder, Headset, Home, IndianRupee, LayoutDashboard, LayoutGrid, Leaf, Link, Lock, MapPin, Minus, Package, Pencil, Phone, Plus, RefreshCcw, Salad, Search, ShieldCheck, ShoppingBasket, ShoppingCart, Sprout, Star, Store, Trash2, Truck, Upload, User, Users } from 'lucide';

const icons = {
    Apple,
    ArrowRight,
    BadgeCheck,
    Banknote,
    Carrot,
    Check,
    ChevronDown,
    ChevronRight,
    CircleCheck,
    Citrus,
    CreditCard,
    Folder,
    Headset,
    Home,
    IndianRupee,
    LayoutDashboard,
    LayoutGrid,
    Leaf,
    Link,
    Lock,
    MapPin,
    Minus,
    Package,
    Pencil,
    Phone,
    Plus,
    RefreshCcw,
    Salad,
    Search,
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

document.addEventListener('livewire:init', () => {
    renderIcons();
    initReveals();

    Livewire.hook('morph.added', () => renderIcons());
    Livewire.hook('morph.updated', () => renderIcons());
});

document.addEventListener('livewire:navigated', () => {
    renderIcons();
    initReveals();
});
