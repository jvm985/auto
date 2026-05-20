<x-layout :title="'Agenda — '.$car->name">
    @php
        $weekdays = ['ma', 'di', 'wo', 'do', 'vr', 'za', 'zo'];
        $months = [1=>'januari','februari','maart','april','mei','juni','juli','augustus','september','oktober','november','december'];
        $today = now()->toDateString();

        $allReservations = collect($days)->pluck('reservations')->flatten()->unique('id')->sortBy('starts_at');
        $reservationData = $allReservations->mapWithKeys(fn ($r) => [
            $r->id => [
                'id' => $r->id,
                'mine' => $r->user_id === auth()->id(),
                'user' => $r->user->name,
                'starts_at' => $r->starts_at->toIso8601String(),
                'ends_at' => $r->ends_at->toIso8601String(),
                'start_date' => $r->starts_at->toDateString(),
                'start_time' => $r->starts_at->format('H:i'),
                'end_date' => $r->ends_at->toDateString(),
                'end_time' => $r->ends_at->format('H:i'),
                'purpose' => $r->purpose,
                'pretty' => $r->starts_at->isoFormat('dd D MMM HH:mm').' – '.
                    ($r->starts_at->isSameDay($r->ends_at)
                        ? $r->ends_at->format('H:i')
                        : $r->ends_at->isoFormat('dd D MMM HH:mm')),
            ],
        ]);
    @endphp

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <a href="{{ route('dashboard') }}" class="mb-1 inline-flex items-center text-sm text-slate-500 hover:text-slate-700">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1 h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Terug naar overzicht
            </a>
            <h1 class="text-3xl font-bold text-slate-900">{{ $car->name }}</h1>
            <p class="text-slate-600">
                {{ trim(($car->brand ?? '').' '.($car->model ?? '')) }}
                @if($car->license_plate)
                    · <span class="font-mono">{{ $car->license_plate }}</span>
                @endif
                · {{ $car->group->name }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('cars.calendar', ['car' => $car, 'year' => $prev->year, 'month' => $prev->month]) }}"
               class="rounded-lg border border-slate-200 bg-white p-2 text-slate-600 hover:bg-slate-50" aria-label="Vorige maand">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="min-w-[10rem] rounded-lg border border-slate-200 bg-white px-4 py-2 text-center text-base font-semibold text-slate-900">
                {{ $months[$cursor->month] }} {{ $cursor->year }}
            </div>
            <a href="{{ route('cars.calendar', ['car' => $car, 'year' => $next->year, 'month' => $next->month]) }}"
               class="rounded-lg border border-slate-200 bg-white p-2 text-slate-600 hover:bg-slate-50" aria-label="Volgende maand">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            <a href="{{ route('cars.calendar', $car) }}"
               class="ml-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Vandaag
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_340px]">
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
                    <div data-date="{{ $dateStr }}"
                         class="calendar-day relative flex h-32 flex-col items-stretch p-1.5 text-left transition hover:bg-indigo-50/40 {{ $bg }}">
                        <button type="button" class="day-header flex items-center justify-between px-1 text-left" data-date="{{ $dateStr }}">
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
                                    $label = '';
                                    if ($startsHere && $endsHere) {
                                        $label = $r->starts_at->format('H:i');
                                    } elseif ($startsHere) {
                                        $label = $r->starts_at->format('H:i').' →';
                                    } elseif ($endsHere) {
                                        $label = '→ '.$r->ends_at->format('H:i');
                                    } else {
                                        $label = '↔';
                                    }
                                @endphp
                                <button type="button"
                                        data-reservation-id="{{ $r->id }}"
                                        class="reservation-chip cursor-pointer truncate rounded-md border px-1.5 py-0.5 text-left text-[11px] font-medium {{ $color }}">
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

        <aside class="space-y-6">
            {{-- Details panel (verschijnt na klik op reservatie) --}}
            <div id="reservation-details" class="hidden rounded-2xl border border-indigo-200 bg-white p-5 shadow-sm ring-1 ring-indigo-100">
                <div class="flex items-start justify-between gap-2">
                    <h3 class="text-base font-semibold text-slate-900">Reservatiedetails</h3>
                    <button type="button" id="close-details" class="rounded-md p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Sluiten">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <dl class="mt-3 space-y-2 text-sm">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Door</dt>
                        <dd id="rd-user" class="font-medium text-slate-900"></dd>
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
                <div id="rd-actions" class="mt-4 hidden gap-2 sm:flex">
                    <button type="button" id="rd-edit"
                            class="flex-1 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Bewerken
                    </button>
                    <form id="rd-delete-form" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full rounded-lg border border-rose-200 bg-white px-3 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50"
                                onclick="return confirm('Reservatie verwijderen?')">
                            Verwijderen
                        </button>
                    </form>
                </div>
            </div>

            {{-- Reservatieformulier (maakt nieuw OF werkt bij) --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 id="form-title" class="text-base font-semibold text-slate-900">Nieuwe reservatie</h3>
                    <button type="button" id="cancel-edit" class="hidden text-xs font-medium text-slate-500 hover:text-slate-700">
                        Annuleer bewerking
                    </button>
                </div>
                <p id="form-hint" class="mt-0.5 text-sm text-slate-500">Klik een dag in de agenda of vul handmatig in. Meerdere dagen mogelijk.</p>
                <form id="reservation-form" method="POST"
                      action="{{ route('reservations.store', $car) }}"
                      data-create-action="{{ route('reservations.store', $car) }}"
                      class="mt-4 space-y-3">
                    @csrf
                    <input type="hidden" name="_method" id="form-method" value="POST">
                    <div>
                        <label class="block text-xs font-medium text-slate-600" for="start_date">Van</label>
                        <div class="mt-1 grid grid-cols-[1fr_auto] gap-2">
                            <input id="start_date" name="start_date" type="date" required
                                   value="{{ old('start_date', $today) }}"
                                   class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none">
                            <input id="start_time" name="start_time" type="time" required
                                   value="{{ old('start_time', '09:00') }}"
                                   class="block w-28 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600" for="end_date">Tot</label>
                        <div class="mt-1 grid grid-cols-[1fr_auto] gap-2">
                            <input id="end_date" name="end_date" type="date" required
                                   value="{{ old('end_date', $today) }}"
                                   class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none">
                            <input id="end_time" name="end_time" type="time" required
                                   value="{{ old('end_time', '17:00') }}"
                                   class="block w-28 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600" for="purpose">Reden (optioneel)</label>
                        <input id="purpose" name="purpose" type="text" maxlength="255"
                               value="{{ old('purpose') }}"
                               placeholder="bv. Boodschappen, weekendje weg..."
                               class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none">
                    </div>
                    <button type="submit" id="submit-button"
                            class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Reservatie maken
                    </button>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-base font-semibold text-slate-900">Mijn reservaties deze maand</h3>
                @php
                    $mine = $allReservations->filter(fn ($r) => $r->user_id === auth()->id());
                @endphp
                @if($mine->isEmpty())
                    <p class="mt-2 text-sm text-slate-500">Nog geen reservaties.</p>
                @else
                    <ul class="mt-3 space-y-2">
                        @foreach($mine as $r)
                            <li>
                                <button type="button"
                                        data-reservation-id="{{ $r->id }}"
                                        class="reservation-chip flex w-full items-start justify-between gap-3 rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-left hover:border-indigo-200 hover:bg-indigo-50">
                                    <span class="min-w-0">
                                        <span class="block text-sm font-medium text-slate-900">
                                            @if($r->starts_at->isSameDay($r->ends_at))
                                                {{ $r->starts_at->isoFormat('dd D MMM') }}
                                            @else
                                                {{ $r->starts_at->isoFormat('D MMM') }} – {{ $r->ends_at->isoFormat('D MMM') }}
                                            @endif
                                        </span>
                                        <span class="block text-xs text-slate-500">
                                            {{ $r->starts_at->format('H:i') }} – {{ $r->ends_at->format('H:i') }}
                                            @if($r->purpose) · {{ $r->purpose }} @endif
                                        </span>
                                    </span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-base font-semibold text-slate-900">Legenda</h3>
                <ul class="mt-2 space-y-1.5 text-sm">
                    <li class="flex items-center gap-2">
                        <span class="inline-block h-3 w-3 rounded border border-indigo-200 bg-indigo-100"></span>
                        <span class="text-slate-700">Mijn reservatie</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="inline-block h-3 w-3 rounded border border-slate-200 bg-slate-100"></span>
                        <span class="text-slate-700">Andere gebruiker</span>
                    </li>
                    <li class="pt-1 text-xs text-slate-500">
                        <strong>Tip:</strong> klik op een reservatie voor details, rechtermuisklik voor snelle acties.
                    </li>
                </ul>
            </div>
        </aside>
    </div>

    {{-- Context menu (rechtermuisklik) --}}
    <div id="context-menu"
         class="fixed z-50 hidden min-w-[10rem] overflow-hidden rounded-lg border border-slate-200 bg-white py-1 text-sm shadow-lg">
        <button type="button" id="ctx-details" class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-slate-700 hover:bg-slate-50">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 text-slate-500">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Details
        </button>
        <button type="button" id="ctx-edit" class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-slate-700 hover:bg-slate-50">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 text-slate-500">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Bewerken
        </button>
        <button type="button" id="ctx-delete" class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-rose-600 hover:bg-rose-50">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>
            </svg>
            Verwijderen
        </button>
    </div>

    {{-- Hidden delete form (used by context menu) --}}
    <form id="ctx-delete-form" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <script>
        const reservations = @json($reservationData);
        const currentUserId = {{ auth()->id() }};
        const deleteUrlBase = "{{ url('reserveringen') }}";
        const updateUrlBase = "{{ url('reserveringen') }}";
        const createAction = document.getElementById('reservation-form').dataset.createAction;

        const form = document.getElementById('reservation-form');
        const methodField = document.getElementById('form-method');
        const formTitle = document.getElementById('form-title');
        const formHint = document.getElementById('form-hint');
        const submitButton = document.getElementById('submit-button');
        const cancelEditBtn = document.getElementById('cancel-edit');
        const detailsPanel = document.getElementById('reservation-details');
        const ctxMenu = document.getElementById('context-menu');
        const ctxDeleteForm = document.getElementById('ctx-delete-form');

        let editingId = null;
        let selectedId = null;
        let ctxTargetId = null;

        function showDetails(id) {
            const r = reservations[id];
            if (!r) return;
            selectedId = id;
            document.getElementById('rd-user').textContent = r.user;
            document.getElementById('rd-start').textContent = new Date(r.starts_at).toLocaleString('nl-BE', {
                weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit',
            });
            document.getElementById('rd-end').textContent = new Date(r.ends_at).toLocaleString('nl-BE', {
                weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit',
            });
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
                actions.classList.add('flex');
                document.getElementById('rd-delete-form').action = deleteUrlBase + '/' + r.id;
            } else {
                actions.classList.add('hidden');
                actions.classList.remove('flex');
            }
            detailsPanel.classList.remove('hidden');
        }

        function hideDetails() {
            detailsPanel.classList.add('hidden');
            selectedId = null;
        }

        function startEdit(id) {
            const r = reservations[id];
            if (!r || !r.mine) return;
            editingId = id;
            form.action = updateUrlBase + '/' + id;
            methodField.value = 'PATCH';
            formTitle.textContent = 'Reservatie bewerken';
            formHint.textContent = 'Pas de gegevens aan en bewaar.';
            submitButton.textContent = 'Bewaar wijzigingen';
            cancelEditBtn.classList.remove('hidden');
            document.getElementById('start_date').value = r.start_date;
            document.getElementById('start_time').value = r.start_time;
            document.getElementById('end_date').value = r.end_date;
            document.getElementById('end_time').value = r.end_time;
            document.getElementById('purpose').value = r.purpose || '';
            form.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function cancelEdit() {
            editingId = null;
            form.action = createAction;
            methodField.value = 'POST';
            formTitle.textContent = 'Nieuwe reservatie';
            formHint.textContent = 'Klik een dag in de agenda of vul handmatig in. Meerdere dagen mogelijk.';
            submitButton.textContent = 'Reservatie maken';
            cancelEditBtn.classList.add('hidden');
        }

        function deleteReservation(id) {
            if (!confirm('Reservatie verwijderen?')) return;
            ctxDeleteForm.action = deleteUrlBase + '/' + id;
            ctxDeleteForm.submit();
        }

        function showCtxMenu(x, y, id) {
            const r = reservations[id];
            if (!r) return;
            ctxTargetId = id;
            const editBtn = document.getElementById('ctx-edit');
            const deleteBtn = document.getElementById('ctx-delete');
            if (r.mine) {
                editBtn.classList.remove('hidden');
                deleteBtn.classList.remove('hidden');
            } else {
                editBtn.classList.add('hidden');
                deleteBtn.classList.add('hidden');
            }
            ctxMenu.style.left = x + 'px';
            ctxMenu.style.top = y + 'px';
            ctxMenu.classList.remove('hidden');
            const rect = ctxMenu.getBoundingClientRect();
            if (rect.right > window.innerWidth) {
                ctxMenu.style.left = (x - rect.width) + 'px';
            }
            if (rect.bottom > window.innerHeight) {
                ctxMenu.style.top = (y - rect.height) + 'px';
            }
        }

        function hideCtxMenu() {
            ctxMenu.classList.add('hidden');
            ctxTargetId = null;
        }

        document.querySelectorAll('.reservation-chip').forEach((el) => {
            el.addEventListener('click', (e) => {
                e.stopPropagation();
                showDetails(parseInt(el.dataset.reservationId, 10));
            });
            el.addEventListener('contextmenu', (e) => {
                e.preventDefault();
                e.stopPropagation();
                showCtxMenu(e.clientX, e.clientY, parseInt(el.dataset.reservationId, 10));
            });
        });

        document.querySelectorAll('.day-header').forEach((el) => {
            el.addEventListener('click', () => {
                if (editingId) return;
                const date = el.dataset.date;
                document.getElementById('start_date').value = date;
                document.getElementById('end_date').value = date;
                document.querySelectorAll('.calendar-day').forEach((c) => c.classList.remove('ring-2','ring-indigo-500','ring-inset'));
                el.closest('.calendar-day').classList.add('ring-2','ring-indigo-500','ring-inset');
            });
        });

        document.getElementById('close-details').addEventListener('click', hideDetails);
        document.getElementById('rd-edit').addEventListener('click', () => selectedId && startEdit(selectedId));
        cancelEditBtn.addEventListener('click', cancelEdit);

        document.getElementById('ctx-details').addEventListener('click', () => {
            if (ctxTargetId) showDetails(ctxTargetId);
            hideCtxMenu();
        });
        document.getElementById('ctx-edit').addEventListener('click', () => {
            if (ctxTargetId) startEdit(ctxTargetId);
            hideCtxMenu();
        });
        document.getElementById('ctx-delete').addEventListener('click', () => {
            const id = ctxTargetId;
            hideCtxMenu();
            if (id) deleteReservation(id);
        });

        document.addEventListener('click', hideCtxMenu);
        document.addEventListener('scroll', hideCtxMenu, true);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                hideCtxMenu();
                hideDetails();
            }
        });

        // Auto-koppel: als start_date verandert en end_date is vroeger, pas end_date aan
        document.getElementById('start_date').addEventListener('change', (e) => {
            const end = document.getElementById('end_date');
            if (end.value < e.target.value) end.value = e.target.value;
        });
    </script>
</x-layout>
