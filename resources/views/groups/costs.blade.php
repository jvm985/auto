<x-layout :title="'Kosten — '.$group->name">
    @php
        $fmtEur = fn ($v) => '€'.number_format((float) $v, 2, ',', '.');
        $shortDate = fn ($d) => \Carbon\Carbon::parse($d)->isoFormat('D MMM');
        $firstName = fn ($u) => $u ? (explode(' ', $u->name ?? $u->email)[0]) : '?';
    @endphp

    <div class="mb-5">
        <a href="{{ route('dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-700">← Terug</a>
        <h1 class="mt-1 text-2xl font-bold text-slate-900 sm:text-3xl">Kosten & km — {{ $group->name }}</h1>
        <p class="mt-1 text-sm text-slate-500">Iedere kost wordt gelijk verdeeld over de {{ $participantCount }} {{ $participantCount === 1 ? 'deelnemer' : 'deelnemers' }}. Kilometers tellen niet mee in de berekening — ze geven enkel inzicht in ieders aandeel.</p>
    </div>

    {{-- ═══ Samenvatting huidige periode ═══ --}}
    <section class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Totale kosten</div>
            <div class="mt-1 text-xl font-bold text-slate-900 sm:text-2xl">{{ $fmtEur($totalCost) }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Per deelnemer</div>
            <div class="mt-1 text-xl font-bold text-slate-900 sm:text-2xl">{{ $fmtEur($share) }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Totale km</div>
            <div class="mt-1 text-xl font-bold text-slate-900 sm:text-2xl">{{ number_format($totalKm, 0, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Deelnemers</div>
            <div class="mt-1 text-xl font-bold text-slate-900 sm:text-2xl">{{ $participantCount }}</div>
        </div>
    </section>

    {{-- ═══ Wie staat hoe ═══ --}}
    @if($perUser->isNotEmpty())
        <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <header class="border-b border-slate-100 px-4 py-3 sm:px-5">
                <h2 class="text-base font-semibold text-slate-900">Stand van zaken</h2>
                <p class="text-xs text-slate-500">Voorlopige verdeling — wordt definitief bij het afsluiten.</p>
            </header>
            <ul class="divide-y divide-slate-100">
                @foreach($perUser as $row)
                    @php
                        $isMe = $row['user']->id === auth()->id();
                        $netClass = $row['net'] > 0
                            ? 'text-emerald-700'
                            : ($row['net'] < 0 ? 'text-rose-700' : 'text-slate-500');
                    @endphp
                    <li class="flex items-center gap-3 px-4 py-3 sm:px-5">
                        @if($row['user']->avatar)
                            <img src="{{ $row['user']->avatar }}" alt="" class="h-9 w-9 shrink-0 rounded-full ring-1 ring-slate-200">
                        @else
                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-200 text-sm font-semibold text-slate-600">
                                {{ strtoupper(substr($firstName($row['user']), 0, 1)) }}
                            </span>
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="truncate font-medium text-slate-900">{{ $firstName($row['user']) }}</span>
                                @if($isMe)
                                    <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700">jij</span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-500">
                                {{ $fmtEur($row['contributed']) }} betaald · {{ number_format($row['km'], 0, ',', '.') }} km
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-base font-bold {{ $netClass }}">
                                {{ $row['net'] >= 0 ? '+' : '' }}{{ $fmtEur($row['net']) }}
                            </div>
                            <div class="text-xs text-slate-400">
                                @if($row['net'] > 0) krijgt terug
                                @elseif($row['net'] < 0) moet bijleggen
                                @else gelijk
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- ═══ Snelle invoer (kost + km) ═══ --}}
    <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="mb-3 flex gap-2" role="tablist">
            <button type="button" data-tab-btn="cost"
                    class="tab-btn flex-1 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm font-semibold text-indigo-700">
                + Kost
            </button>
            <button type="button" data-tab-btn="km"
                    class="tab-btn flex-1 rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700">
                + Kilometers
            </button>
        </div>

        {{-- Kost formulier --}}
        <form data-tab-panel="cost" method="POST" action="{{ route('expenses.store', $group) }}" class="space-y-3">
            @csrf
            <div>
                <label for="cost_amount" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Bedrag (€)</label>
                <input id="cost_amount" name="amount" type="number" step="0.01" min="0.01" inputmode="decimal" required
                       value="{{ old('amount') }}" placeholder="0,00"
                       class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-3 text-base shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none">
            </div>
            <div>
                <label for="cost_description" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Waarvoor?</label>
                <input id="cost_description" name="description" type="text" required maxlength="255"
                       value="{{ old('description') }}" placeholder="bv. tankbeurt, carwash, verzekering"
                       class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-3 text-base shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="cost_car_id" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Auto</label>
                    <select id="cost_car_id" name="car_id" required
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-3 text-base shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none">
                        @foreach($group->cars as $car)
                            <option value="{{ $car->id }}" @selected(old('car_id') == $car->id)>{{ $car->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="cost_incurred_at" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Datum</label>
                    <input id="cost_incurred_at" name="incurred_at" type="date" required
                           value="{{ old('incurred_at', $today) }}" max="{{ $today }}"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-3 text-base shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none">
                </div>
            </div>
            <button type="submit"
                    class="w-full rounded-lg bg-indigo-600 px-4 py-3 text-base font-semibold text-white shadow-sm hover:bg-indigo-700 active:bg-indigo-800">
                Kost toevoegen
            </button>
        </form>

        {{-- Km formulier --}}
        <form data-tab-panel="km" method="POST" action="{{ route('mileage.store', $group) }}" class="hidden space-y-3">
            @csrf
            <div>
                <label for="km_kilometers" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Aantal km</label>
                <input id="km_kilometers" name="kilometers" type="number" min="1" step="1" inputmode="numeric" required
                       value="{{ old('kilometers') }}" placeholder="bv. 42"
                       class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-3 text-base shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="km_car_id" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Auto</label>
                    <select id="km_car_id" name="car_id" required
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-3 text-base shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none">
                        @foreach($group->cars as $car)
                            <option value="{{ $car->id }}" @selected(old('car_id') == $car->id)>{{ $car->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="km_driven_at" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Datum</label>
                    <input id="km_driven_at" name="driven_at" type="date" required
                           value="{{ old('driven_at', $today) }}" max="{{ $today }}"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-3 text-base shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none">
                </div>
            </div>
            <div>
                <label for="km_description" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Omschrijving (optioneel)</label>
                <input id="km_description" name="description" type="text" maxlength="255"
                       value="{{ old('description') }}" placeholder="bv. boodschappen, familiebezoek"
                       class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-3 text-base shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 focus:outline-none">
            </div>
            <button type="submit"
                    class="w-full rounded-lg bg-indigo-600 px-4 py-3 text-base font-semibold text-white shadow-sm hover:bg-indigo-700 active:bg-indigo-800">
                Kilometers toevoegen
            </button>
        </form>
    </section>

    {{-- ═══ Lijst: kosten ═══ --}}
    <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <header class="border-b border-slate-100 px-4 py-3 sm:px-5">
            <h2 class="text-base font-semibold text-slate-900">Kosten deze periode</h2>
            <p class="text-xs text-slate-500">{{ $openExpenses->count() }} {{ $openExpenses->count() === 1 ? 'kost' : 'kosten' }} · {{ $fmtEur($totalCost) }} totaal</p>
        </header>
        @if($openExpenses->isEmpty())
            <div class="px-4 py-8 text-center text-sm text-slate-500">Nog geen kosten ingebracht.</div>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach($openExpenses as $e)
                    @php $isMine = $e->user_id === auth()->id(); @endphp
                    <li class="flex items-center gap-3 px-4 py-3 sm:px-5">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-base font-semibold text-slate-900">{{ $fmtEur($e->amount) }}</span>
                                <span class="truncate text-sm text-slate-700">{{ $e->description }}</span>
                            </div>
                            <div class="mt-0.5 text-xs text-slate-500">
                                {{ $shortDate($e->incurred_at) }} · {{ $e->car?->name }} · {{ $firstName($e->user) }}
                                @if($isMine)
                                    <span class="ml-1 rounded-full bg-indigo-100 px-1.5 py-0.5 text-[10px] font-medium text-indigo-700">jij</span>
                                @endif
                            </div>
                        </div>
                        @if($isMine)
                            <form method="POST" action="{{ route('expenses.destroy', [$group, $e]) }}"
                                  onsubmit="return confirm('Deze kost verwijderen?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" aria-label="Verwijderen"
                                        class="rounded-lg border border-rose-200 bg-white p-2 text-rose-600 hover:bg-rose-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>
                                    </svg>
                                </button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- ═══ Lijst: km ═══ --}}
    <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <header class="border-b border-slate-100 px-4 py-3 sm:px-5">
            <h2 class="text-base font-semibold text-slate-900">Kilometers deze periode</h2>
            <p class="text-xs text-slate-500">{{ $openMileage->count() }} {{ $openMileage->count() === 1 ? 'rit' : 'ritten' }} · {{ number_format($totalKm, 0, ',', '.') }} km totaal</p>
        </header>
        @if($openMileage->isEmpty())
            <div class="px-4 py-8 text-center text-sm text-slate-500">Nog geen kilometers ingebracht.</div>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach($openMileage as $m)
                    @php $isMine = $m->user_id === auth()->id(); @endphp
                    <li class="flex items-center gap-3 px-4 py-3 sm:px-5">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-base font-semibold text-slate-900">{{ number_format($m->kilometers, 0, ',', '.') }} km</span>
                                @if($m->description)
                                    <span class="truncate text-sm text-slate-700">{{ $m->description }}</span>
                                @endif
                            </div>
                            <div class="mt-0.5 text-xs text-slate-500">
                                {{ $shortDate($m->driven_at) }} · {{ $m->car?->name }} · {{ $firstName($m->user) }}
                                @if($isMine)
                                    <span class="ml-1 rounded-full bg-indigo-100 px-1.5 py-0.5 text-[10px] font-medium text-indigo-700">jij</span>
                                @endif
                            </div>
                        </div>
                        @if($isMine)
                            <form method="POST" action="{{ route('mileage.destroy', [$group, $m]) }}"
                                  onsubmit="return confirm('Deze rit verwijderen?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" aria-label="Verwijderen"
                                        class="rounded-lg border border-rose-200 bg-white p-2 text-rose-600 hover:bg-rose-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>
                                    </svg>
                                </button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- ═══ Afsluiten (alleen beheerder) ═══ --}}
    @if($canClose)
        <section class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm sm:p-5">
            <h2 class="text-base font-semibold text-amber-900">Maand afsluiten</h2>
            <p class="mt-1 text-sm text-amber-800">
                Alle openstaande kosten en km tot en met de gekozen datum worden vastgeklikt in een afrekening. Daarna staan de tellers op nul.
            </p>
            <form method="POST" action="{{ route('settlements.store', $group) }}"
                  onsubmit="return confirm('Afrekening maken? Dit kan niet meer ongedaan worden gemaakt.');"
                  class="mt-3 grid gap-3 sm:grid-cols-[1fr_auto]">
                @csrf
                <div>
                    <label for="period_end" class="block text-xs font-medium uppercase tracking-wide text-amber-900">Periode tot en met</label>
                    <input id="period_end" name="period_end" type="date" required
                           value="{{ old('period_end', $defaultPeriodEnd) }}" max="{{ $today }}"
                           class="mt-1 block w-full rounded-lg border border-amber-300 bg-white px-3 py-3 text-base shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-100 focus:outline-none">
                </div>
                <button type="submit"
                        class="self-end rounded-lg bg-amber-600 px-5 py-3 text-base font-semibold text-white shadow-sm hover:bg-amber-700 active:bg-amber-800">
                    Sluit af
                </button>
            </form>
        </section>
    @endif

    {{-- ═══ Vorige afrekeningen ═══ --}}
    <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <header class="border-b border-slate-100 px-4 py-3 sm:px-5">
            <h2 class="text-base font-semibold text-slate-900">Vorige afrekeningen</h2>
        </header>
        @if($settlements->isEmpty())
            <div class="px-4 py-8 text-center text-sm text-slate-500">Nog geen afrekeningen.</div>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach($settlements as $s)
                    <li>
                        <a href="{{ route('settlements.show', [$group, $s]) }}"
                           class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 sm:px-5">
                            <div class="min-w-0 flex-1">
                                <div class="font-medium text-slate-900">
                                    @if($s->period_start)
                                        {{ \Carbon\Carbon::parse($s->period_start)->isoFormat('D MMM') }} – {{ \Carbon\Carbon::parse($s->period_end)->isoFormat('D MMM YYYY') }}
                                    @else
                                        Tot en met {{ \Carbon\Carbon::parse($s->period_end)->isoFormat('D MMM YYYY') }}
                                    @endif
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ $fmtEur($s->total_cost) }} · {{ $s->participant_count }} pers · {{ $fmtEur($s->share_per_participant) }}/pers
                                </div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4 text-slate-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <script>
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const which = btn.dataset.tabBtn;
                document.querySelectorAll('.tab-btn').forEach(b => {
                    const active = b.dataset.tabBtn === which;
                    b.className = (active
                        ? 'tab-btn flex-1 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm font-semibold text-indigo-700'
                        : 'tab-btn flex-1 rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700');
                });
                document.querySelectorAll('[data-tab-panel]').forEach(p => {
                    p.classList.toggle('hidden', p.dataset.tabPanel !== which);
                });
            });
        });
    </script>
</x-layout>
