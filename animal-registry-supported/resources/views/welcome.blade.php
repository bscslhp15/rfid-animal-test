<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Animal Registry PROTOTYPE</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-100 text-slate-800 antialiased">
        <div class="min-h-screen flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-5xl rounded-2xl border border-slate-200 bg-white shadow-xl overflow-hidden">
                <div class="bg-indigo-600 px-8 py-6 text-white">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-indigo-100">Veterinary ....</p>
                            <h1 class="mt-2 text-3xl font-bold">Animal Registry PROTOTYPE</h1>
                        </div>
                        <div class="text-right">
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}" class="inline-flex items-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-indigo-700 shadow-sm hover:bg-indigo-50">Log in</a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="grid gap-10 p-8 md:grid-cols-2">
                    <div class="space-y-6">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-indigo-600">Overview</p>
                            <h2 class="mt-3 text-4xl font-bold tracking-tight text-slate-900">Animal records for better care and faster traceability.</h2>
                        </div>

                    

                        <div class="flex flex-wrap gap-3">
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}" class="rounded-md bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Open dashboard</a>
                            @endif
                            <a href="{{ route('register') }}" class="rounded-md border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Create account</a>
                        </div>

                       
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6">
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Current Function of the prototype</p>
                        <ul class="mt-5 space-y-4 text-slate-700">
                            <li class="flex gap-3"><span class="mt-1 inline-block h-2.5 w-2.5 rounded-full bg-indigo-500"></span><span>Animal registration</span></li>
                            <li class="flex gap-3"><span class="mt-1 inline-block h-2.5 w-2.5 rounded-full bg-indigo-500"></span><span>QR-token based lookup route for public record access(wala pang rfid)</span></li>
                            <li class="flex gap-3"><span class="mt-1 inline-block h-2.5 w-2.5 rounded-full bg-indigo-500"></span><span>Dashboard summary and recent records view,overview,analytics and s on</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @include('rfid.listener', ['lookupUrl' => route('rfid.public-scan.lookup')])
    </body>
</html>
