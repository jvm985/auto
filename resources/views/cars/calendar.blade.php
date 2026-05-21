<x-layout :title="'Agenda — '.$car->name">
    @php
        $months = [1=>'januari','februari','maart','april','mei','juni','juli','augustus','september','oktober','november','december'];
        $weekdays = ['ma', 'di', 'wo', 'do', 'vr', 'za', 'zo'];
        $today = now()->toDateString();
        $allReservations = collect($days)->pluck('reservations')->flatten()->unique('id')->sortBy('starts_at');
        $inMonth = $allReservations->filter(fn ($r) => $r->starts_at->month === $cursor->month && $r->starts_at->year === $cursor->year);

        $reservationData = $allReservations->mapWithKeys(fn ($r) => [
            $r->id => [
                'id' => $r->id,
                'mine' => $r->user_id === auth()->id(),
                'user' => $r->user->name,
                'start_date' => $r->starts_at->toDateString(),
                'start_time' => $r->starts_at->format('H:i'),
                'end_date' => $r->ends_at->toDateString(),
                'end_time' => $r->ends_at->format('H:i'),
                'purpose' => $r->purpose,
                'pretty_start' => $r->starts_at->isoFormat('dd D MMM HH:mm'),
                'pretty_end' => $r->ends_at->isoFormat('dd D MMM HH:mm'),
            ],
        ]);

        // Group in-month reservations by start date for the agenda list.
        $byDay = $inMonth->groupBy(fn ($r) => $r->starts_at->toDateString());
    @endphp

    {{-- Header --}}
    <div class="mb-5">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-700">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1 h-4 w-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Terug
        </a>
        <h1 class="mt-1 text-2xl font-bold text-slate-900 sm:text-3xl">{{ $car->name }}</h1>
        <p class="text-sm text-slate-600 sm:text-base">
            {{ trim(($car->brand ?? '').' '.($car->model ?? '')) }}
            @if($car->license_plate) · <span class="font-mono">{{ $car->license_plate }}</span> @endif
            · {{ $car->group->name }}
        </p>
    </div>

    {{-- Month navigator: big tap-targets --}}
    <div class="mb-4 flex items-stretch gap-2">
        <a href="{{ route('cars.calendar', ['car' => $car, 'year' => $prev->year, 'month' => $prev->month]) }}"
           aria-label="Vorige maand"
           class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-slate-600 hover:bg-slate-50 active:bg-slate-100">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex flex-1 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-3 text-base font-semibold text-slate-900">
            {{ $months[$cursor->month] }} {{ $cursor->year }}
        </div>
        <a href="{{ route('cars.calendar', ['car' => $car, 'year' => $next->year, 'month' => $next->month]) }}"
           aria-label="Volgende maand"
           class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-slate-600 hover:bg-slate-50 active:bg-slate-100">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @if($cursor->month !== now()->month || $cursor->year !== now()->year)
            <a href="{{ route('cars.calendar', $car) }}"
               class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Vandaag
            </a>
        @endif
    </div>

    {{-- ═══════════════ Agenda lijst (mobiel + tablet) ═══════════════ --}}
    <section class="mb-24 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:hidden">
        @if($byDay->isEmpty())
            <div class="px-4 py-10 text-center text-sm text-slate-500">
                Geen reservaties in {{ $months[$cursor->month] }}.<br>
                Tik op <span class="font-medium text-indigo-600">+ Nieuwe reservatie</span> hieronder.
            </div>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach($byDay as $dateStr => $resForDay)
                    @php
                        $d = \Carbon\Carbon::parse($dateStr);
                        $isToday = $d->isToday();
                    @endphp
                    <li>
                        <button type="button" data-new-on-date="{{ $dateStr }}"
                                class="day-add flex w-full items-center justify-between gap-3 bg-slate-50 px-4 py-2 text-left hover:bg-indigo-50">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    {{ $d->isoFormat('dddd D MMM') }}
                                </span>
                                @if($isToday)
                                    <span class="rounded-full bg-indigo-600 px-2 py-0.5 text-[10px] font-semibold uppercase text-white">vandaag</span>
                                @endif
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4 text-slate-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                        <ul>
                            @foreach($resForDay as $r)
                                @php
                                    $mine = $r->user_id === auth()->id();
                                    $bar = $mine ? 'border-indigo-500 bg-indigo-50' : 'border-slate-300 bg-white';
                                    $startsHere = $r->starts_at->toDateString() === $dateStr;
                                    $endsHere = $r->ends_at->toDateString() === $dateStr;
                                @endphp
                                <li>
                                    <button type="button" data-reservation-id="{{ $r->id }}"
                                            class="reservation-row flex w-full items-center gap-3 border-l-4 px-4 py-3 text-left {{ $bar }} hover:bg-indigo-100/40">
                                        <div class="min-w-0 flex-1">
                                            <div class="text-base font-semibold text-slate-900">
                                                @if($startsHere && $endsHere)
                                                    {{ $r->starts_at->format('H:i') }} – {{ $r->ends_at->format('H:i') }}
                                                @elseif($startsHere)
                                                    Vanaf {{ $r->starts_at->format('H:i') }} → {{ $r->ends_at->isoFormat('dd D MMM HH:mm') }}
                                                @elseif($endsHere)
                                                    Tot {{ $r->ends_at->format('H:i') }}
                                                @else
                                                    Hele dag
                                                @endif
                                            </div>
                                            <div class="mt-0.5 text-sm text-slate-600">
                                                {{ explode(' ', $r->user->name)[0] }}
                                                @if($mine) <span class="ml-1 rounded-full bg-indigo-100 px-1.5 py-0.5 text-[10px] font-medium text-indigo-700">jij</span> @endif
                                                @if($r->purpose) · <span class="text-slate-500">{{ $r->purpose }}</span> @endif
                                            </div>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4 text-slate-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- ═══════════════ Maandraster (alleen desktop ≥ lg) ═══════════════ --}}
    <section class="mb-8 hidden lg:block">
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-50 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">
                @foreach($weekdays as $wd)
                    <div class="px-2 py-2">{{ $wd }}</div>
                @endforeach
            </div>
            <div class="grid grid-cols-7 divide-x divide-y divide-slate-100">
                @foreach($days as $day)
                    @php
                        $dateStr = $day['date']->toDateString();
                        $bg = !$day['in_month'] ? 'bg-slate-50/60' : 'bg-white';
                        $textColor = $day['in_month'] ? 'text-slate-900' : 'text-slate-400';
                    @endphp
                    <div class="relative flex h-32 flex-col items-stretch p-1.5 text-left transition hover:bg-indigo-50/40 {{ $bg }}">
                        <button type="button" class="day-add flex items-center justify-between px-1 text-left"
                                data-new-on-date="{{ $dateStr }}">
                            <span class="text-sm font-semibold {{ $textColor }} @if($day['is_today']) inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-white @endif">
                                {{ $day['date']->day }}
                            </span>
                            @if($day['reservations']->count() > 0)
                                <span class="rounded-full bg-indigo-100 px-1.5 text-xs font-medium text-indigo-700">
                                    {{ $day['reservations']->count() }}
                                </span>
                            @endif
                        </button>
                        <div class="mt-1 flex flex-col gap-0.5 overflow-hidden">
                            @foreach($day['reservations']->take(3) as $r)
                                @php
                                    $mine = $r->user_id === auth()->id();
                                    $color = $mine
                                        ? 'bg-indigo-100 text-indigo-800 border-indigo-200 hover:bg-indigo-200'
                                        : 'bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200';
                                    $startsHere = $r->starts_at->toDateString() === $dateStr;
                                    $endsHere = $r->ends_at->toDateString() === $dateStr;
                                    $label = match (true) {
                                        $startsHere && $endsHere => $r->starts_at->format('H:i'),
                                        $startsHere => $r->starts_at->format('H:i').' →',
                                        $endsHere => '→ '.$r->ends_at->format('H:i'),
                                        default => '↔',
                                    };
                                @endphp
                                <button type="button" data-reservation-id="{{ $r->id }}"
                                        class="reservation-row cursor-pointer truncate rounded-md border px-1.5 py-0.5 text-left text-[11px] font-medium {{ $color }}">
                                    <span class="font-semibold">{{ $label }}</span>
                                    <span class="ml-1">{{ explode(' ', $r->user->name)[0] }}</span>
                                </button>
                            @endforeach
                            @if($day['reservations']->count() > 3)
                                <div class="px-1.5 text-[11px] text-slate-500">+{{ $day['reservations']->count() - 3 }} meer</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════ Sticky FAB: nieuwe reservatie ═══════════════ --}}
    <div class="pointer-events-none fixed inset-x-0 bottom-0 z-30 flex justify-center px-4 pb-4 sm:pb-6">
        <button type="button" id="open-new"
                class="pointer-events-auto inline-flex w-full max-w-sm items-center justify-center gap-2 rounded-full bg-indigo-600 px-6 py-4 text-base font-semibold text-white shadow-lg shadow-indigo-200 ring-1 ring-indigo-700/20 transition hover:bg-indigo-700 active:bg-indigo-800">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Nieuwe reservatie
        </button>
    </div>

    {{-- ═══════════════ Bottom-sheet / dialog ═══════════════ --}}
    <div id="sheet-backdrop" class="fixed inset-0 z-40 hidden bg-slate-900/40" aria-hidden="true"></div>
    <div id="sheet"
         class="fixed inset-x-0 bottom-0 z-50 hidden max-h-[92dvh] translate-y-full overflow-y-auto rounded-t-2xl bg-white shadow-2xl transition-transform sm:inset-x-auto sm:bottom-auto sm:left-1/2 sm:top-1/2 sm:w-full sm:max-w-md sm:-translate-x-1/2 sm:-translate-y-1/2 sm:rounded-2xl"
         role="dialog" aria-modal="true">
        <div class="sticky top-0 z-10 border-b border-slate-200 bg-white px-5 py-3">
            <div class="mx-auto mb-2 h-1 w-10 rounded-full bg-slate-300 sm:hidden"></div>
            <div class="flex items-center justify-between gap-3">
                <h3 id="sheet-title" class="text-lg font-semibold text-slate-900">Nieuwe reservatie</h3>
                <button type="button" id="sheet-close" aria-label="Sluiten"
                        class="rounded-md p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mode: details (read-only) --}}
        <div id="sheet-details" class="hidden px-5 py-4">
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Door</dt>
                    <dd id="rd-user" class="text-base font-medium text-slate-900"></dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Van</dt>
                    <dd id="rd-start" class="text-slate-900"></dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Tot</dt>
                    <dd id="rd-end" class="text-slate-900"></dd>
                </div>
                <div id="rd-purpose-row" class="hidden">
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Reden</dt>
                    <dd id="rd-purpose" class="text-slate-900"></dd>
                </div>
            </dl>
            <div id="rd-actions" class="mt-5 hidden grid grid-cols-2 gap-2">
                <button type="button" id="rd-edit"
                        class="rounded-lg bg-indigo-600 px-4 py-3 text-base font-semibold text-white hover:bg-indigo-700 active:bg-indigo-800">
                    Bewerken
                </button>
                <form id="rd-delete-form" method="POST" class="contents">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            onclick="return confirm('Reservatie verwijderen?')"
                            class="rounded-lg border border-rose-200 bg-white px-4 py-3 text-base font-semibold text-rose-700 hover:bg-rose-50 active:bg-rose-100">
                        Verwijderen
                    </button>
                </form>
            </div>
        </div>

        {{-- Mode: form (new/edit) --}}
        <form id="reservation-form" method="POST"
              action="{{ route('reservations.store', $car) }}"
              data-create-action="{{ route('reservations.store', $car) }}"
              class="px-5 py-4 space-y-4">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">

            <div>
                <label for="start_date" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Van</label>
                <div class="mt-1 grid grid-cols-[1fr_auto] gap-2">
                    <input id="start_date" name="start_date" type="date" required
                           value="{{ old('start_date', $today) }}"
                           class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-3 text-base shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none">
                    <input id="start_time" name="start_time" type="time" required
                           value="{{ old('start_time', '09:00') }}"
                           class="block w-28 rounded-lg border border-slate-300 bg-white px-3 py-3 text-base shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none">
                </div>
            </div>
            <div>
                <label for="end_date" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Tot</label>
                <div class="mt-1 grid grid-cols-[1fr_auto] gap-2">
                    <input id="end_date" name="end_date" type="date" required
                           value="{{ old('end_date', $today) }}"
                           class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-3 text-base shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none">
                    <input id="end_time" name="end_time" type="time" required
                           value="{{ old('end_time', '17:00') }}"
                           class="block w-28 rounded-lg border border-slate-300 bg-white px-3 py-3 text-base shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none">
                </div>
            </div>
            <div>
                <label for="purpose" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Reden (optioneel)</label>
                <input id="purpose" name="purpose" type="text" maxlength="255"
                       value="{{ old('purpose') }}"
                       placeholder="bv. boodschappen, weekendje weg"
                       class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-3 text-base shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none">
            </div>
            <button type="submit" id="submit-button"
                    class="w-full rounded-lg bg-indigo-600 px-4 py-3 text-base font-semibold text-white shadow-sm hover:bg-indigo-700 active:bg-indigo-800">
                Reservatie maken
            </button>
        </form>
    </div>

    <script>
        const reservations = @json($reservationData);
        const deleteUrlBase = "{{ url('reserveringen') }}";
        const updateUrlBase = "{{ url('reserveringen') }}";
        const createAction = document.getElementById('reservation-form').dataset.createAction;

        const sheet = document.getElementById('sheet');
        const backdrop = document.getElementById('sheet-backdrop');
        const sheetTitle = document.getElementById('sheet-title');
        const detailsPanel = document.getElementById('sheet-details');
        const form = document.getElementById('reservation-form');
        const methodField = document.getElementById('form-method');
        const submitButton = document.getElementById('submit-button');

        let editingId = null;
        let selectedId = null;

        function openSheet() {
            backdrop.classList.remove('hidden');
            sheet.classList.remove('hidden');
            requestAnimationFrame(() => sheet.classList.remove('translate-y-full'));
            document.body.style.overflow = 'hidden';
        }

        function closeSheet() {
            sheet.classList.add('translate-y-full');
            backdrop.classList.add('hidden');
            // delay hidden so the slide animation can play
            setTimeout(() => {
                sheet.classList.add('hidden');
                detailsPanel.classList.add('hidden');
                form.classList.remove('hidden');
            }, 200);
            document.body.style.overflow = '';
            selectedId = null;
            // reset to create mode after close
            cancelEdit();
        }

        function showDetails(id) {
            const r = reservations[id];
            if (!r) return;
            selectedId = id;
            sheetTitle.textContent = 'Reservatie';
            form.classList.add('hidden');
            detailsPanel.classList.remove('hidden');

            document.getElementById('rd-user').textContent = r.user;
            document.getElementById('rd-start').textContent = r.pretty_start;
            document.getElementById('rd-end').textContent = r.pretty_end;
            const purposeRow = document.getElementById('rd-purpose-row');
            if (r.purpose) {
                purposeRow.classList.remove('hidden');
                document.getElementById('rd-purpose').textContent = r.purpose;
            } else {
                purposeRow.classList.add('hidden');
            }
            const actions = document.getElementById('rd-actions');
            if (r.mine) {
                actions.classList.remove('hidden');
                actions.classList.add('grid');
                document.getElementById('rd-delete-form').action = deleteUrlBase + '/' + r.id;
            } else {
                actions.classList.add('hidden');
                actions.classList.remove('grid');
            }
            openSheet();
        }

        function showNewForm(date) {
            sheetTitle.textContent = 'Nieuwe reservatie';
            detailsPanel.classList.add('hidden');
            form.classList.remove('hidden');
            cancelEdit();
            if (date) {
                document.getElementById('start_date').value = date;
                document.getElementById('end_date').value = date;
            }
            openSheet();
        }

        function startEdit(id) {
            const r = reservations[id];
            if (!r || !r.mine) return;
            editingId = id;
            sheetTitle.textContent = 'Reservatie bewerken';
            detailsPanel.classList.add('hidden');
            form.classList.remove('hidden');
            form.action = updateUrlBase + '/' + id;
            methodField.value = 'PATCH';
            submitButton.textContent = 'Bewaar wijzigingen';
            document.getElementById('start_date').value = r.start_date;
            document.getElementById('start_time').value = r.start_time;
            document.getElementById('end_date').value = r.end_date;
            document.getElementById('end_time').value = r.end_time;
            document.getElementById('purpose').value = r.purpose || '';
        }

        function cancelEdit() {
            editingId = null;
            form.action = createAction;
            methodField.value = 'POST';
            submitButton.textContent = 'Reservatie maken';
        }

        document.querySelectorAll('.reservation-row').forEach((el) => {
            el.addEventListener('click', (e) => {
                e.stopPropagation();
                showDetails(parseInt(el.dataset.reservationId, 10));
            });
        });

        document.querySelectorAll('.day-add').forEach((el) => {
            el.addEventListener('click', (e) => {
                e.stopPropagation();
                showNewForm(el.dataset.newOnDate);
            });
        });

        document.getElementById('open-new').addEventListener('click', () => showNewForm(null));
        document.getElementById('sheet-close').addEventListener('click', closeSheet);
        backdrop.addEventListener('click', closeSheet);
        document.getElementById('rd-edit').addEventListener('click', () => {
            if (selectedId) startEdit(selectedId);
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !sheet.classList.contains('hidden')) closeSheet();
        });

        // Auto-link end_date to start_date when start moves later
        document.getElementById('start_date').addEventListener('change', (e) => {
            const end = document.getElementById('end_date');
            if (end.value < e.target.value) end.value = e.target.value;
        });

        @if ($errors->any() || old('start_date'))
            // Re-open sheet if there were validation errors on submit
            showNewForm(null);
            @if (old('start_date'))
                document.getElementById('start_date').value = @json(old('start_date'));
                document.getElementById('start_time').value = @json(old('start_time'));
                document.getElementById('end_date').value = @json(old('end_date'));
                document.getElementById('end_time').value = @json(old('end_time'));
                document.getElementById('purpose').value = @json(old('purpose'));
            @endif
        @endif
    </script>
</x-layout>
