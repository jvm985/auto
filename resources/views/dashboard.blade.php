<x-layout :title="'Mijn autodeelgroepen'">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">Goeiedag, {{ explode(' ', auth()->user()->name)[0] }}.</h1>
        <p class="mt-1 text-sm text-slate-600 sm:text-base">Kies een auto, breng kosten in of bekijk wie wat moet.</p>
    </div>

    @forelse($groups as $group)
        <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <header class="flex items-baseline justify-between gap-3 border-b border-slate-100 px-4 py-3 sm:px-5">
                <h2 class="text-lg font-semibold text-slate-900 sm:text-xl">{{ $group->name }}</h2>
                @if($group->city)
                    <span class="text-xs text-slate-500 sm:text-sm">{{ $group->city }}</span>
                @endif
            </header>

            {{-- Auto's: grote tap-targets, één kolom op mobiel, twee op tablet+ --}}
            <div class="grid grid-cols-1 gap-px bg-slate-100 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($group->cars as $car)
                    <a href="{{ route('cars.calendar', $car) }}"
                       class="group flex items-center gap-4 bg-white px-4 py-4 transition hover:bg-indigo-50/40 sm:px-5">
                        <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-xl"
                              style="background: {{ $car->color ?: '#e0e7ff' }}33; color: {{ $car->color ?: '#4f46e5' }};">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-6 w-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 11l1.5-4.5A2 2 0 018.4 5h7.2a2 2 0 011.9 1.5L19 11M5 11h14M5 11v6a1 1 0 001 1h1a1 1 0 001-1v-1h8v1a1 1 0 001 1h1a1 1 0 001-1v-6M7 14h.01M17 14h.01"/>
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="font-semibold text-slate-900">{{ $car->name }}</div>
                            <div class="truncate text-sm text-slate-500">
                                {{ trim(($car->brand ?? '').' '.($car->model ?? '')) ?: 'Open agenda' }}
                                @if($car->license_plate) · <span class="font-mono">{{ $car->license_plate }}</span> @endif
                            </div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5 text-slate-400 group-hover:text-indigo-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @empty
                    <div class="bg-white px-4 py-6 text-center text-sm text-slate-500 sm:px-5">
                        Deze groep heeft nog geen auto's.
                    </div>
                @endforelse
            </div>

            {{-- Quick links: kosten + leden --}}
            <div class="grid grid-cols-2 gap-px border-t border-slate-100 bg-slate-100">
                <a href="{{ route('groups.costs.index', $group) }}"
                   class="flex items-center justify-center gap-2 bg-white px-4 py-3 text-sm font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-700">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Kosten &amp; km
                </a>
                <a href="{{ route('groups.members.index', $group) }}"
                   class="flex items-center justify-center gap-2 bg-white px-4 py-3 text-sm font-medium text-slate-700 hover:bg-indigo-50 hover:text-indigo-700">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Leden
                </a>
            </div>
        </section>
    @empty
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center">
            <h2 class="text-lg font-semibold text-slate-900">Je zit nog in geen enkele autodeelgroep</h2>
            <p class="mt-2 text-sm text-slate-500">Vraag een groepsbeheerder om je toe te voegen.</p>
        </div>
    @endforelse
</x-layout>
