<section class="relative isolate overflow-hidden">
    <div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top,_var(--color-indigo-100),_transparent_60%)] dark:bg-[radial-gradient(ellipse_at_top,_var(--color-indigo-950),_transparent_60%)]"></div>

    <div class="mx-auto grid max-w-6xl gap-12 px-6 py-16 lg:grid-cols-5 lg:px-8 lg:py-24">
        <div class="flex flex-col gap-6 lg:col-span-2">
            <span class="w-fit rounded-full bg-indigo-50 px-3 py-1 text-sm font-medium text-indigo-700 ring-1 ring-indigo-600/20 dark:bg-indigo-500/10 dark:text-indigo-300 dark:ring-indigo-400/30">
                Free to register
            </span>

            <h1 class="text-4xl font-semibold tracking-tight text-balance sm:text-5xl">Save your seat</h1>

            <p class="text-lg text-pretty text-slate-600 dark:text-slate-400">
                Sign up for an upcoming conference and we'll keep your spot. It takes less than a minute.
            </p>

            <ul class="flex flex-col gap-3 text-sm text-slate-600 dark:text-slate-400">
                @foreach (['Talks from speakers who ship', 'Meet attendees from across the region', 'One sign-up per email for each conference'] as $perk)
                    <li class="flex items-center gap-3" wire:key="perk-{{ $loop->index }}">
                        <x-filament::icon icon="heroicon-m-check-circle" class="size-5 text-indigo-600 dark:text-indigo-400" />
                        {{ $perk }}
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="lg:col-span-3">
            <div class="rounded-3xl bg-white p-6 shadow-xl ring-1 ring-slate-200 sm:p-10 dark:bg-slate-900 dark:ring-slate-800">
                @if ($attendee)
                    <div class="flex flex-col items-center gap-5 py-6 text-center">
                        <span class="grid size-14 place-items-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                            <x-filament::icon icon="heroicon-o-check" class="size-7" />
                        </span>

                        <div class="flex flex-col gap-2">
                            <h2 class="text-2xl font-semibold tracking-tight">You're signed up, {{ $attendee->name }}!</h2>
                            <p class="text-slate-600 dark:text-slate-400">
                                We've saved your seat at <span class="font-medium text-slate-900 dark:text-white">{{ $attendee->conference->name }}</span>
                                on {{ $attendee->conference->dateRange() }}@if ($attendee->conference->venue) in {{ $attendee->conference->venue->city }}@endif.
                            </p>
                        </div>

                        <div class="flex flex-wrap justify-center gap-3">
                            <a href="{{ route('home') }}" class="rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500">
                                Back to conferences
                            </a>
                            <button type="button" wire:click="signUpAnother" class="rounded-full px-5 py-2.5 text-sm font-semibold ring-1 ring-slate-300 transition hover:ring-slate-400 dark:ring-slate-700">
                                Sign up someone else
                            </button>
                        </div>
                    </div>
                @elseif ($this->listedConferences->isEmpty())
                    <div class="flex flex-col items-center gap-3 py-10 text-center">
                        <h2 class="text-xl font-semibold">No conferences are open for sign-up</h2>
                        <p class="text-slate-600 dark:text-slate-400">Check back soon. New conferences are added regularly.</p>
                        <a href="{{ route('home') }}" class="mt-2 text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">Back to conferences</a>
                    </div>
                @else
                    <form wire:submit="signUp" class="flex flex-col gap-6">
                        <div class="flex flex-col gap-1">
                            <h2 class="text-xl font-semibold tracking-tight">Attendee details</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400">We'll only use your email for updates about this conference.</p>
                        </div>

                        {{ $this->form }}

                        <x-filament::button type="submit" size="lg" class="w-full">
                            Sign up
                        </x-filament::button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <x-filament-actions::modals />
</section>
