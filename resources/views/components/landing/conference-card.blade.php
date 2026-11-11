@props(['conference'])

@php
    /** @var \App\Models\Conference $conference */
    $image = $conference->venue?->getFirstMediaUrl('venue-images');
@endphp

<article class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900">
    <div class="relative aspect-[16/9] overflow-hidden bg-gradient-to-br from-indigo-500 via-violet-500 to-fuchsia-500">
        @if (filled($image))
            <img src="{{ $image }}" alt="{{ $conference->venue->name }}" class="size-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
        @endif

        <span class="absolute top-4 left-4 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-slate-900 backdrop-blur">
            {{ $conference->status->getLabel() }}
        </span>
    </div>

    <div class="flex flex-1 flex-col gap-3 p-6">
        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{ $conference->dateRange() }}</p>

        <h3 class="text-xl font-semibold tracking-tight">{{ $conference->name }}</h3>

        <p class="line-clamp-3 text-sm text-slate-600 dark:text-slate-400">
            {{ Str::of($conference->description)->markdown()->stripTags()->squish() }}
        </p>

        <dl class="mt-auto flex flex-wrap gap-x-5 gap-y-2 border-t border-slate-100 pt-4 text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">
            @if ($conference->venue)
                <div class="flex items-center gap-1.5">
                    <dt class="sr-only">Location</dt>
                    <x-filament::icon icon="heroicon-m-map-pin" class="size-4" />
                    <dd>{{ $conference->venue->city }}, {{ $conference->venue->country }}</dd>
                </div>
            @endif
            <div class="flex items-center gap-1.5">
                <dt class="sr-only">Speakers</dt>
                <x-filament::icon icon="heroicon-m-user-group" class="size-4" />
                <dd>{{ trans_choice(':count speaker|:count speakers', $conference->speakers_count) }}</dd>
            </div>
            <div class="flex items-center gap-1.5">
                <dt class="sr-only">Talks</dt>
                <x-filament::icon icon="heroicon-m-microphone" class="size-4" />
                <dd>{{ trans_choice(':count talk|:count talks', $conference->talks_count) }}</dd>
            </div>
        </dl>

        <a href="{{ route('sign-up', ['conference' => $conference->getKey()]) }}" class="rounded-full bg-indigo-50 px-4 py-2 text-center text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-300 dark:hover:bg-indigo-500/20">
            Sign up
        </a>
    </div>
</article>
