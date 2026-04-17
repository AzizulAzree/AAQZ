<x-app-layout>
    <div class="py-12 print:py-0">
        <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8 print:max-w-none print:space-y-0 print:px-0">
            @include('bpp.printables.partials.toolbar', ['previewTitle' => $previewTitle, 'bpp' => $bpp])
            @include('bpp.printables.partials.pages.page-two-page')
        </div>
    </div>
</x-app-layout>
