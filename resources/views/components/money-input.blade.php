@props([
    'entangled' => 'amount',
    'label' => null,
    'placeholder' => '0,00',
    'required' => false,
])

@php
    $controlClasses = \Flux\Flux::classes()
        ->add('w-full border rounded-lg block disabled:shadow-none dark:shadow-none')
        ->add('appearance-none')
        ->add('text-base sm:text-sm py-2 h-10 leading-[1.375rem]')
        ->add('ps-3 pe-3')
        ->add('bg-white dark:bg-white/10 dark:disabled:bg-white/[7%]')
        ->add('text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 dark:text-zinc-300 dark:disabled:text-zinc-400 dark:placeholder-zinc-400 dark:disabled:placeholder-zinc-500')
        ->add('shadow-xs border-zinc-200 border-b-zinc-300/80 disabled:border-b-zinc-200 dark:border-white/10 dark:disabled:border-white/5')
        ->add('data-invalid:shadow-none data-invalid:border-red-500 dark:data-invalid:border-red-500 disabled:data-invalid:border-red-500 dark:disabled:data-invalid:border-red-500')
        ->add($attributes->pluck('class:input'))
        ->add($attributes->get('class', ''));
@endphp

@once
    <script>
        window.brMoneyFmt = function (dot) {
            if (dot === null || dot === undefined || dot === '') {
                return '';
            }
            const n = parseFloat(String(dot).replace(',', '.'));
            if (Number.isNaN(n)) {
                return '';
            }
            return new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
        };

        /**
         * Formata enquanto digita (milhares com "." e vírgula decimal), sem forçar 2 casas até o blur.
         */
        window.brMoneyFmtTyping = function (raw) {
            if (raw === null || raw === undefined) {
                return '';
            }
            let s = String(raw).replace(/\u00A0/g, ' ').trim().replace(/\s+/g, '');
            if (s === '') {
                return '';
            }

            const fmtInt = function (intDigits) {
                const n = intDigits.replace(/\D/g, '');
                if (n === '') {
                    return '';
                }
                const num = parseInt(n, 10);
                if (Number.isNaN(num)) {
                    return '';
                }
                return String(num).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            };

            const lastComma = s.lastIndexOf(',');
            const lastDot = s.lastIndexOf('.');

            if (lastComma !== -1 && (lastDot === -1 || lastComma > lastDot)) {
                const before = s.slice(0, lastComma).replace(/\./g, '');
                const after = s.slice(lastComma + 1).replace(/\D/g, '').slice(0, 2);
                const intDigits = before.replace(/\D/g, '');
                const intFmt = fmtInt(intDigits);
                if (after.length > 0) {
                    return (intFmt === '' ? '0' : intFmt) + ',' + after;
                }
                // Sem dígitos antes nem depois da vírgula: não deixar "," preso ao apagar
                if (intFmt === '') {
                    return '';
                }
                return intFmt + ',';
            }

            if (lastDot !== -1 && lastComma === -1) {
                const parts = s.split('.');
                if (parts.length === 2 && parts[1].length <= 2 && /^\d+$/.test(parts[1])) {
                    const intDigits = parts[0].replace(/\D/g, '');
                    const intFmt = fmtInt(intDigits);
                    return (intFmt === '' ? '0' : intFmt) + ',' + parts[1];
                }
                const allDigits = s.replace(/\./g, '').replace(/\D/g, '');
                return fmtInt(allDigits);
            }

            return fmtInt(s.replace(/\D/g, ''));
        };

        window.brMoneyParse = function (input) {
            if (input === null || input === undefined) {
                return '';
            }
            let s = String(input).replace(/\u00A0/g, ' ').trim().replace(/\s+/g, '');
            if (s === '') {
                return '';
            }
            const lastComma = s.lastIndexOf(',');
            const lastDot = s.lastIndexOf('.');
            if (lastComma !== -1 && (lastDot === -1 || lastComma > lastDot)) {
                let intPart = s.slice(0, lastComma).replace(/\./g, '');
                let decPart = s.slice(lastComma + 1).replace(/\D/g, '').slice(0, 2);
                intPart = intPart.replace(/\D/g, '');
                if (intPart === '' && decPart === '') {
                    return '';
                }
                return decPart === '' ? intPart : intPart + '.' + decPart;
            }
            if (lastDot !== -1 && lastComma === -1) {
                const parts = s.split('.');
                if (parts.length === 2 && parts[1].length <= 2 && /^\d+$/.test(parts[1])) {
                    const intPart = parts[0].replace(/\D/g, '');
                    return intPart === '' ? '' : intPart + '.' + parts[1];
                }
                return s.replace(/\./g, '').replace(/\D/g, '');
            }
            return s.replace(/\D/g, '');
        };
    </script>
@endonce

<flux:field>
    @if ($label)
        <flux:label>{{ $label }}</flux:label>
    @endif
    <div
        wire:ignore
        x-data="{
            value: @entangle($entangled),
            display: '',
            focused: false,
            init() {
                this.$watch('value', () => {
                    if (!this.focused) {
                        this.display = window.brMoneyFmt(this.value);
                    }
                });
                this.display = window.brMoneyFmt(this.value);
            },
            onFocus() {
                this.focused = true;
            },
            onBlur() {
                this.focused = false;
                this.value = window.brMoneyParse(this.display);
                this.display = window.brMoneyFmt(this.value);
            },
            onInput(e) {
                const raw = e.target.value;
                const digitsBefore = (function (str, pos) {
                    let n = 0;
                    for (let i = 0; i < pos && i < str.length; i++) {
                        if (/\d/.test(str[i])) {
                            n++;
                        }
                    }
                    return n;
                })(raw, e.target.selectionStart ?? raw.length);

                this.value = window.brMoneyParse(raw);
                this.display = window.brMoneyFmtTyping(raw);

                queueMicrotask(() => {
                    const el = e.target;
                    let pos = this.display.length;
                    if (digitsBefore <= 0) {
                        pos = 0;
                    } else {
                        let seen = 0;
                        for (let i = 0; i < this.display.length; i++) {
                            if (/\d/.test(this.display[i])) {
                                seen++;
                                if (seen >= digitsBefore) {
                                    pos = i + 1;
                                    break;
                                }
                            }
                        }
                    }
                    el.setSelectionRange(pos, pos);
                });
            },
        }"
    >
        <div class="w-full relative block group/input" data-flux-input>
            <input
                type="text"
                inputmode="decimal"
                autocomplete="off"
                data-flux-control
                data-flux-group-target
                placeholder="{{ $placeholder }}"
                @if ($required)
                    required
                @endif
                @if ($errors->has($entangled))
                    aria-invalid="true"
                    data-invalid
                @endif
                class="{{ $controlClasses }}"
                x-bind:value="display"
                x-on:focus="onFocus()"
                x-on:blur="onBlur()"
                x-on:input="onInput($event)"
            />
        </div>
    </div>
    <flux:error name="{{ $entangled }}" />
</flux:field>
