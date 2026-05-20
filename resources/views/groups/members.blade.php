<x-layout :title="'Leden — '.$group->name">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-700">← Terug</a>
            <h1 class="mt-1 text-3xl font-bold text-slate-900">Leden — {{ $group->name }}</h1>
            @if($group->city)
                <p class="mt-1 text-sm text-slate-500">{{ $group->city }}</p>
            @endif
        </div>
    </div>

    @if($isAdmin)
        <section class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Lid toevoegen</h2>
            <p class="mt-1 text-sm text-slate-500">
                Vul het emailadres in. Als die persoon nog niet ingelogd is, wordt hij bij de eerste Google-login automatisch gekoppeld.
            </p>
            <form method="POST" action="{{ route('groups.members.store', $group) }}" class="mt-4 grid gap-3 sm:grid-cols-[1fr_1fr_auto]">
                @csrf
                <label class="block">
                    <span class="text-xs font-medium uppercase tracking-wide text-slate-500">Emailadres</span>
                    <input type="email" name="email" required value="{{ old('email') }}"
                           class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </label>
                <label class="block">
                    <span class="text-xs font-medium uppercase tracking-wide text-slate-500">Naam (optioneel)</span>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </label>
                <div class="flex items-end gap-3">
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="is_admin" value="1" {{ old('is_admin') ? 'checked' : '' }}
                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Beheerder
                    </label>
                    <button type="submit"
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                        Toevoegen
                    </button>
                </div>
            </form>
        </section>
    @endif

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <ul class="divide-y divide-slate-200">
            @foreach($members as $member)
                <li class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3 min-w-0">
                        @if($member->avatar)
                            <img src="{{ $member->avatar }}" alt="" class="h-10 w-10 rounded-full ring-1 ring-slate-200">
                        @else
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-200 text-sm font-semibold text-slate-600">
                                {{ strtoupper(substr($member->name ?: $member->email, 0, 1)) }}
                            </span>
                        @endif
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-slate-900">{{ $member->name ?: '(nog niet ingelogd)' }}</span>
                                @if($member->pivot->is_admin)
                                    <span class="inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                        Beheerder
                                    </span>
                                @endif
                                @if(! $member->google_id)
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">
                                        Uitgenodigd
                                    </span>
                                @endif
                            </div>
                            <div class="text-sm text-slate-500 truncate">{{ $member->email }}</div>
                        </div>
                    </div>

                    @if($isAdmin)
                        <div class="flex flex-wrap items-center gap-2">
                            <form method="POST" action="{{ route('groups.members.update', [$group, $member]) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="is_admin" value="{{ $member->pivot->is_admin ? 0 : 1 }}">
                                <button type="submit"
                                        class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                    {{ $member->pivot->is_admin ? 'Beheerder af' : 'Maak beheerder' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('groups.members.destroy', [$group, $member]) }}"
                                  onsubmit="return confirm('Weet je zeker dat je {{ $member->email }} uit de groep wilt verwijderen?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="rounded-lg border border-rose-200 bg-white px-3 py-1.5 text-sm font-medium text-rose-700 hover:bg-rose-50">
                                    Verwijderen
                                </button>
                            </form>
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
    </section>
</x-layout>
