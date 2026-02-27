<x-public-layout :restaurant="$restaurant">
    <div class="bg-white dark:bg-gray-800">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
            <div class="text-center">
                <p class="text-base font-semibold text-indigo-600 dark:text-indigo-400">{{ $restaurant->name }}</p>
                <h1 class="mt-1 text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-5xl">Meny</h1>
                <p class="mx-auto mt-5 max-w-xl text-xl text-gray-500 dark:text-gray-300">Vårt urval av mat och dryck.</p>
                 <div class="mt-8">
                    <a href="{{ url()->previous() }}" class="text-base font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                        &larr; Tillbaka
                    </a>
                </div>
            </div>
        </div>
    </div>

    @php
        $foodLabels = ['starters' => 'Förrätter & snacks', 'mains' => 'Huvudrätter', 'desserts' => 'Desserter', 'other_food' => 'Övrig mat'];
        $drinkLabels = ['non_alcoholic' => 'Alkoholfritt', 'beer_cider' => 'Öl & cider', 'wine' => 'Vin', 'cocktails' => 'Cocktails', 'coffee_tea' => 'Kaffe & te', 'other_drinks' => 'Övrig dryck'];
    @endphp

    <div class="mx-auto max-w-4xl space-y-16 px-4 py-16 sm:px-6 lg:px-8">
        @if($foodGroups->isNotEmpty())
            <div class="space-y-10">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl text-center">Mat</h2>
                @foreach($foodGroups as $key => $group)
                    <div class="border-t border-gray-200 pt-10 dark:border-gray-700">
                        <h3 class="text-2xl font-semibold leading-7 text-gray-900 dark:text-white">{{ $foodLabels[$key] ?? 'Mat' }}</h3>
                        <div class="mt-8 divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($group as $item)
                                <div class="py-5">
                                    <div class="flex justify-between">
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $item->name }}</h4>
                                        <p class="text-lg font-medium text-gray-900 dark:text-white">{{ number_format($item->price, 0, ',', ' ') }} kr</p>
                                    </div>
                                    @if($item->description)
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $item->description }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($drinkGroups->isNotEmpty())
            <div class="space-y-10">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl text-center">Dryck</h2>
                 @foreach($drinkGroups as $key => $group)
                    <div class="border-t border-gray-200 pt-10 dark:border-gray-700">
                        <h3 class="text-2xl font-semibold leading-7 text-gray-900 dark:text-white">{{ $drinkLabels[$key] ?? 'Dryck' }}</h3>
                        <div class="mt-8 divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($group as $item)
                                <div class="py-5">
                                    <div class="flex justify-between">
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $item->name }}</h4>
                                        <p class="text-lg font-medium text-gray-900 dark:text-white">{{ number_format($item->price, 0, ',', ' ') }} kr</p>
                                    </div>
                                    @if($item->description)
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $item->description }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($foodGroups->isEmpty() && $drinkGroups->isEmpty())
             <div class="py-16 text-center">
                <p class="text-gray-500">Inga menyartiklar tillgängliga just nu.</p>
            </div>
        @endif
    </div>
</x-public-layout>