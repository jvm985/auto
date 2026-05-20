<x-layout :title="'Mijn autodeelgroepen'">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900">Goeiedag, {{ explode(' ', auth()->user()->name)[0] }}.</h1>
        <p class="mt-1 text-slate-600">Kies een auto uit een van je groepen om een reservatie te maken.</p>
    </div>

    @forelse($groups as $group)
        <section class="mb-8">
            <div class="mb-3 flex items-baseline justify-between gap-3">
                <h2 class="text-xl font-semibold text-slate-900">{{ $group->name }}</h2>
                <div class="flex items-baseline gap-3">
                    @if($group->city)
                        <span class="text-sm text-slate-500">{{ $group->city }}</span>
                    @endif
                    <a href="{{ route('groups.members.index', $group) }}"
                       class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                        Leden &amp; beheerders →
                    </a>
                </div>
            </div>

            @if($group->cars->isEmpty())
                <div class="rounded-xl border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-500">
                    Deze groep heeft nog geen auto's.
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($group->cars as $car)
                        <a href="{{ route('cars.calendar', $car) }}"
                           class="group relative flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <div class="flex items-start gap-3">
                                <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-xl"
                                      style="background: {{ $car->color ?: '#e0e7ff' }}33; color: {{ $car->color ?: '#4f46e5' }};">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-6 w-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 11l1.5-4.5A2 2 0 018.4 5h7.2a2 2 0 011.9 1.5L19 11M5 11h14M5 11v6a1 1 0 001 1h1a1 1 0 001-1v-1h8v1a1 1 0 001 1h1a1 1 0 001-1v-6M7 14h.01M17 14h.01"/>
                                    </svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="font-semibold text-slate-900">{{ $car->name }}</div>
                                    <div class="text-sm text-slate-500">
                                        {{ trim(($car->brand ?? '').' '.($car->model ?? '')) ?: 'Auto' }}
                                    </div>
                                    @if($car->license_plate)
                                        <div class="mt-1 inline-block rounded-md bg-slate-100 px-2 py-0.5 font-mono text-xs text-slate-600">
                                            {{ $car->license_plate }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <span class="mt-4 inline-flex items-center text-sm font-medium text-indigo-600 group-hover:text-indigo-700">
                                Open agenda
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ml-1 h-4 w-4 transition group-hover:translate-x-0.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    @empty
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
            <h2 class="text-lg font-semibold text-slate-900">Je zit nog in geen enkele autodeelgroep</h2>
            <p class="mt-2 text-sm text-slate-500">Vraag een groepsbeheerder om je toe te voegen.</p>
        </div>
    @endforelse
</x-layout>
