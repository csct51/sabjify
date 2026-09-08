<div wire:ignore
    x-data="{
        initPicker() {
            if (! window.SlimSelect) return;
            const el = this.$refs.select;
            if (el._ssPicker) { try { el._ssPicker.destroy(); } catch (e) {} delete el._ssPicker; }
            const data = @js($options).map((o) => ({ value: String(o.value), text: o.text, data: { search: o.search || '' } }));
            const initial = @js($selected !== null ? (string) $selected : null);
            el._ssPicker = new window.SlimSelect({
                select: el,
                data: data,
                settings: {
                    placeholderText: @js($placeholder),
                    searchPlaceholder: @js($searchPlaceholder),
                    searchHighlight: true,
                    allowDeselect: true,
                    closeOnSelect: true,
                },
                events: {
                    searchFilter: (option, search) => {
                        const q = (search || '').trim().toLowerCase();
                        if (! q) return true;
                        const hay = ((option.text || '') + ' ' + ((option.data && option.data.search) || '')).toLowerCase();
                        return q.split(/\s+/).every((w) => hay.includes(w));
                    },
                    afterChange: (vals) => {
                        const raw = vals.length ? vals[0].value : '';
                        $wire.set(@js($target), raw === '' ? null : (/^\d+$/.test(raw) ? parseInt(raw, 10) : raw));
                    },
                },
            });
            if (initial !== null && initial !== '') {
                el._ssPicker.setSelected([initial], false);
            }
        },
        resetPicker() {
            const el = this.$refs.select;
            if (el && el._ssPicker) el._ssPicker.setSelected([], false);
        },
    }"
    x-init="initPicker()"
    @if ($clearEvent) x-on:{{ $clearEvent }}.window="resetPicker()" @endif
    class="ss-brand text-sm">
    <select x-ref="select" data-ss-picker aria-label="{{ $placeholder }}">
        <option value=""></option>
    </select>
</div>
