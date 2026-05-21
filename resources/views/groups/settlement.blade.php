<x-layout :title="'Afrekening — '.$group->name">
    @php
        $fmtEur = fn ($v) => '€'.number_format((float) $v, 2, ',', '.');
        $firstName = fn ($u) => $u ? (explode(' ', $u->name ?? $u->email)[0]) : '?';
    @endphp

    <div class="mb-5">
        <a href="{{ route('groups.costs.index', $group) }}" class="text-sm text-indigo-600 hover:text-indigo-700">← Terug naar kosten</a>
        <h1 class="mt-1 text-2xl font-bold text-slate-900 sm:text-3xl">
            Afrekening {{ \Carbon\Carbon::parse($settlement->period_end)->isoFormat('MMMM YYYY') }}
        </h1>
        <p class="mt-1 text-sm text-slate-500">
            @if($settlement->period_start)
                {{ \Carbon\Carbon::parse($settlement->period_start)->isoFormat('D MMM') }} – {{ \Carbon\Carbon::parse($settlement->period_end)->isoFormat('D MMM YYYY') }}
            @else
                Tot en met {{ \Carbon\Carbon::parse($settlement->period_end)->isoFormat('D MMM YYYY') }}
            @endif
            · afgesloten door {{ $firstName($settlement->closedBy) }} op {{ $settlement->created_at->isoFormat('D MMM YYYY') }}
        </p>
    </div>

    <section class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Totale kosten</div>
            <div class="mt-1 text-xl font-bold text-slate-900 sm:text-2xl">{{ $fmtEur($settlement->total_cost) }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Per deelnemer</div>
            <div class="mt-1 text-xl font-bold text-slate-900 sm:text-2xl">{{ $fmtEur($settlement->share_per_participant) }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Totale km</div>
            <div class="mt-1 text-xl font-bold text-slate-900 sm:text-2xl">{{ number_format($settlement->total_km, 0, ',', '.') }}</div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Deelnemers</div>
            <div class="mt-1 text-xl font-bold text-slate-900 sm:text-2xl">{{ $settlement->participant_count }}</div>
        </div>
    </section>

    <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <header class="border-b border-slate-100 px-4 py-3 sm:px-5">
            <h2 class="text-base font-semibold text-slate-900">Definitieve verdeling</h2>
        </header>
        <ul class="divide-y divide-slate-100">
            @foreach($settlement->lines as $line)
                @php
                    $isMe = $line->user_id === auth()->id();
                    $netClass = $line->net > 0
                        ? 'text-emerald-700'
                        : ($line->net < 0 ? 'text-rose-700' : 'text-slate-500');
                @endphp
                <li class="flex items-center gap-3 px-4 py-3 sm:px-5">
                    @if($line->user?->avatar)
                        <img src="{{ $line->user->avatar }}" alt="" class="h-9 w-9 shrink-0 rounded-full ring-1 ring-slate-200">
                    @else
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-200 text-sm font-semibold text-slate-600">
                            {{ strtoupper(substr($firstName($line->user), 0, 1)) }}
                        </span>
                    @endif
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="truncate font-medium text-slate-900">{{ $firstName($line->user) }}</span>
                            @if($isMe)
                                <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700">jij</span>
                            @endif
                        </div>
                        <div class="text-xs text-slate-500">
                            {{ $fmtEur($line->contributed) }} betaald · {{ number_format($line->kilometers, 0, ',', '.') }} km
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-base font-bold {{ $netClass }}">
                            {{ $line->net >= 0 ? '+' : '' }}{{ $fmtEur($line->net) }}
                        </div>
                        <div class="text-xs text-slate-400">
                            @if($line->net > 0) krijgt terug
                            @elseif($line->net < 0) moet bijleggen
                            @else gelijk
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>

    @if($settlement->expenses->isNotEmpty())
        <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <header class="border-b border-slate-100 px-4 py-3 sm:px-5">
                <h2 class="text-base font-semibold text-slate-900">Kosten in deze afrekening</h2>
            </header>
            <ul class="divide-y divide-slate-100">
                @foreach($settlement->expenses as $e)
                    <li class="px-4 py-3 sm:px-5">
                        <div class="flex items-center gap-2">
                            <span class="text-base font-semibold text-slate-900">{{ $fmtEur($e->amount) }}</span>
                            <span class="truncate text-sm text-slate-700">{{ $e->description }}</span>
                        </div>
                        <div class="mt-0.5 text-xs text-slate-500">
                            {{ \Carbon\Carbon::parse($e->incurred_at)->isoFormat('D MMM') }} · {{ $e->car?->name }} · {{ $firstName($e->user) }}
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if($settlement->mileageEntries->isNotEmpty())
        <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <header class="border-b border-slate-100 px-4 py-3 sm:px-5">
                <h2 class="text-base font-semibold text-slate-900">Kilometers in deze afrekening</h2>
            </header>
            <ul class="divide-y divide-slate-100">
                @foreach($settlement->mileageEntries as $m)
                    <li class="px-4 py-3 sm:px-5">
                        <div class="flex items-center gap-2">
                            <span class="text-base font-semibold text-slate-900">{{ number_format($m->kilometers, 0, ',', '.') }} km</span>
                            @if($m->description)
                                <span class="truncate text-sm text-slate-700">{{ $m->description }}</span>
                            @endif
                        </div>
                        <div class="mt-0.5 text-xs text-slate-500">
                            {{ \Carbon\Carbon::parse($m->driven_at)->isoFormat('D MMM') }} · {{ $m->car?->name }} · {{ $firstName($m->user) }}
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</x-layout>
