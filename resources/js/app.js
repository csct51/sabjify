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

document.addEventListener('livewire:init', () => {
    renderIcons();

    Livewire.hook('morph.added', () => renderIcons());
    Livewire.hook('morph.updated', () => renderIcons());
});

document.addEventListener('livewire:navigated', () => {
    renderIcons();
});
