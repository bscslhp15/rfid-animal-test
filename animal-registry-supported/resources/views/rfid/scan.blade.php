<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Scan RFID Tag</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow-sm sm:p-8">
                <p class="text-sm text-gray-600">
                    Scan a registered animal tag with the RFID reader. Keep the input focused; the matching animal record will open automatically.
                </p>

                <form id="rfid-scan-form" method="POST" action="{{ route('rfid.scan.lookup') }}" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label for="rfid_tag_id" class="block text-sm font-medium text-gray-700">RFID tag number</label>
                        <input
                            id="rfid_tag_id"
                            name="rfid_tag_id"
                            type="text"
                            inputmode="numeric"
                            autocomplete="off"
                            autocapitalize="off"
                            spellcheck="false"
                            maxlength="64"
                            autofocus
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 font-mono text-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            aria-describedby="rfid-scan-help"
                        >
                        <p id="rfid-scan-help" class="mt-2 text-xs text-gray-500">
                            The reader must send the tag number as keyboard input. Scanning submits automatically; you can also press Enter.
                        </p>
                        @error('rfid_tag_id')
                            <p class="mt-2 text-sm font-medium text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            Find animal
                        </button>
                        <a href="{{ route('animals.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Back to animals</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const form = document.getElementById('rfid-scan-form');
            const input = document.getElementById('rfid_tag_id');
            let submitTimer;
            let submitted = false;

            const submitScan = () => {
                if (!submitted && input.value.trim()) {
                    submitted = true;
                    input.value = input.value.trim();
                    form.requestSubmit();
                }
            };

            input.addEventListener('input', () => {
                submitted = false;
                window.clearTimeout(submitTimer);
                submitTimer = window.setTimeout(submitScan, 900);
            });

            input.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    window.clearTimeout(submitTimer);
                    submitScan();
                }
            });

            form.addEventListener('submit', () => {
                submitted = true;
            });

            window.addEventListener('pageshow', () => input.focus());
        })();
    </script>
</x-app-layout>
