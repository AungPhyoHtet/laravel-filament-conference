<x-layouts.app :title="config('app.name').' — Conferences worth travelling for'">
    {{-- Hero --}}
    <section class="relative isolate overflow-hidden">
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top,_var(--color-indigo-100),_transparent_60%)] dark:bg-[radial-gradient(ellipse_at_top,_var(--color-indigo-950),_transparent_60%)]"></div>

        <div class="mx-auto grid max-w-7xl items-center gap-12 px-6 py-20 lg:grid-cols-2 lg:px-8 lg:py-28">
            <div class="flex flex-col gap-6">
                <span class="w-fit rounded-full bg-indigo-50 px-3 py-1 text-sm font-medium text-indigo-700 ring-1 ring-indigo-600/20 dark:bg-indigo-500/10 dark:text-indigo-300 dark:ring-indigo-400/30">
                    {{ trans_choice(':count conference on the calendar|:count conferences on the calendar', $stats['Conferences']) }}
                </span>

                <h1 class="text-4xl font-semibold tracking-tight text-balance sm:text-6xl">
                    Conferences worth
                    <span class="bg-gradient-to-r from-indigo-600 to-fuchsia-500 bg-clip-text text-transparent">travelling for</span>
                </h1>

                <p class="max-w-xl text-lg text-pretty text-slate-600 dark:text-slate-400">
                    Hand-picked events with speakers who ship. Find your next conference, meet the people behind the talks and plan your trip.
                </p>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('sign-up') }}" class="rounded-full bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Sign up now
                    </a>
                    <a href="#conferences" class="rounded-full px-6 py-3 text-sm font-semibold text-slate-900 ring-1 ring-slate-300 transition hover:ring-slate-400 dark:text-white dark:ring-slate-700 dark:hover:ring-slate-500">
                        Browse conferences
                    </a>
                </div>
            </div>

            @if ($featured)
                @php($featuredImage = $featured->venue?->getFirstMediaUrl('venue-images'))

                <article class="relative overflow-hidden rounded-3xl bg-slate-900 text-white shadow-2xl ring-1 ring-white/10">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-violet-600 to-fuchsia-600">
                        @if (filled($featuredImage))
                            <img src="{{ $featuredImage }}" alt="{{ $featured->venue->name }}" class="size-full object-cover opacity-40">
                        @endif
                    </div>

                    <div class="relative flex flex-col gap-5 p-8 sm:p-10">
                        <p class="text-sm font-semibold tracking-widest text-white/70 uppercase">Next up · {{ $featured->status->getLabel() }}</p>
                        <h2 class="text-3xl font-semibold tracking-tight">{{ $featured->name }}</h2>
                        <p class="line-clamp-3 text-white/80">
                            {{ Str::of($featured->description)->markdown()->stripTags()->squish() }}
                        </p>

                        <dl class="grid grid-cols-2 gap-4 border-t border-white/20 pt-5 text-sm">
                            <div>
                                <dt class="text-white/60">When</dt>
                                <dd class="font-medium">{{ $featured->dateRange() }}</dd>
                            </div>
                            @if ($featured->venue)
                                <div>
                                    <dt class="text-white/60">Where</dt>
                                    <dd class="font-medium">{{ $featured->venue->name }}, {{ $featured->venue->city }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-white/60">Speakers</dt>
                                <dd class="font-medium">{{ $featured->speakers_count }}</dd>
                            </div>
                            <div>
                                <dt class="text-white/60">Talks</dt>
                                <dd class="font-medium">{{ $featured->talks_count }}</dd>
                            </div>
                        </dl>

                        <a href="{{ route('sign-up', ['conference' => $featured->getKey()]) }}" class="w-fit rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50">
                            Save your seat
                        </a>
                    </div>
                </article>
            @endif
        </div>
    </section>

    {{-- Stats --}}
    <section class="border-y border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-900/50" aria-label="At a glance">
        <dl class="mx-auto grid max-w-7xl grid-cols-2 gap-8 px-6 py-12 lg:grid-cols-4 lg:px-8">
            @foreach ($stats as $label => $value)
                <div class="flex flex-col gap-1 text-center">
                    <dt class="text-sm text-slate-500 dark:text-slate-400">{{ $label }}</dt>
                    <dd class="text-4xl font-semibold tracking-tight">{{ number_format($value) }}</dd>
                </div>
            @endforeach
        </dl>
    </section>

    {{-- Conferences --}}
    <section id="conferences" class="mx-auto flex max-w-7xl scroll-mt-20 flex-col gap-10 px-6 py-20 lg:px-8">
        <div class="flex flex-col gap-3">
            <h2 class="text-3xl font-semibold tracking-tight sm:text-4xl">More on the calendar</h2>
            <p class="max-w-2xl text-slate-600 dark:text-slate-400">Upcoming and ongoing conferences, soonest first.</p>
        </div>

        @if ($conferences->isEmpty())
            <p class="rounded-2xl border border-dashed border-slate-300 p-10 text-center text-slate-500 dark:border-slate-700 dark:text-slate-400">
                {{ $featured ? 'No other conferences are scheduled yet. Check back soon.' : 'No conferences are scheduled yet. Check back soon.' }}
            </p>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($conferences as $conference)
                    <x-landing.conference-card :conference="$conference" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- Speakers --}}
    @if ($speakers->isNotEmpty())
        <section id="speakers" class="scroll-mt-20 bg-slate-50 dark:bg-slate-900/50">
            <div class="mx-auto flex max-w-7xl flex-col gap-10 px-6 py-20 lg:px-8">
                <div class="flex flex-col gap-3">
                    <h2 class="text-3xl font-semibold tracking-tight sm:text-4xl">Featured speakers</h2>
                    <p class="max-w-2xl text-slate-600 dark:text-slate-400">The people taking the stage this season.</p>
                </div>

                <ul class="grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($speakers as $speaker)
                        <li class="flex flex-col items-center gap-3 rounded-2xl bg-white p-6 text-center shadow-sm ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">
                            <img src="{{ $speaker->avatarUrl() }}" alt="" class="size-20 rounded-full object-cover ring-4 ring-indigo-50 dark:ring-indigo-500/10" loading="lazy">
                            <div class="flex flex-col gap-1">
                                <p class="font-semibold">{{ $speaker->name }}</p>
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ trans_choice(':count approved talk|:count approved talks', $speaker->talks_count) }}
                                </p>
                            </div>
                            @if ($twitterUrl = $speaker->twitterUrl())
                                <a href="{{ $twitterUrl }}" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                    {{ '@'.ltrim($speaker->twitter_handle, '@') }}
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- Talks --}}
    @if ($talks->isNotEmpty())
        <section id="talks" class="mx-auto flex max-w-7xl scroll-mt-20 flex-col gap-10 px-6 py-20 lg:px-8">
            <div class="flex flex-col gap-3">
                <h2 class="text-3xl font-semibold tracking-tight sm:text-4xl">On the programme</h2>
                <p class="max-w-2xl text-slate-600 dark:text-slate-400">A taste of the talks already confirmed.</p>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                @foreach ($talks as $talk)
                    <article class="flex flex-col gap-4 rounded-2xl border border-slate-200 p-6 dark:border-slate-800">
                        <span class="w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            {{ $talk->length->getLabel() }}
                        </span>
                        <h3 class="text-lg font-semibold tracking-tight">{{ $talk->title }}</h3>
                        <p class="line-clamp-3 text-sm text-slate-600 dark:text-slate-400">{{ $talk->plainAbstract() }}</p>
                        <div class="mt-auto flex items-center gap-3 border-t border-slate-100 pt-4 dark:border-slate-800">
                            <img src="{{ $talk->speaker->avatarUrl(80) }}" alt="" class="size-9 rounded-full object-cover" loading="lazy">
                            <p class="text-sm font-medium">{{ $talk->speaker->name }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Call to action --}}
    <section class="px-6 pb-20 lg:px-8">
        <div class="mx-auto flex max-w-7xl flex-col items-center gap-6 rounded-3xl bg-gradient-to-r from-indigo-600 to-fuchsia-600 px-6 py-16 text-center text-white sm:px-16">
            <h2 class="text-3xl font-semibold tracking-tight text-balance sm:text-4xl">Running a conference?</h2>
            <p class="max-w-xl text-white/80">Manage venues, speakers and talk submissions in one place, then publish your event here.</p>
            <a href="{{ route('filament.app.auth.login') }}" class="rounded-full bg-white px-6 py-3 text-sm font-semibold text-indigo-700 shadow-sm transition hover:bg-indigo-50">
                Organizer sign in
            </a>
        </div>
    </section>
</x-layouts.app>
