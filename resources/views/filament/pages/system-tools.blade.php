<x-filament-panels::page>
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    Cache & deployment helpers
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                    Use these after uploading new files to the server. The most common reason new admin
                    menu items don't appear after a deploy is stale Filament/route caches or PHP's
                    OPcache holding old class definitions.
                </p>

                <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-3 text-sm text-amber-900 dark:text-amber-200">
                    <p class="font-semibold mb-1">Recommended order after a deploy:</p>
                    <ol class="list-decimal list-inside space-y-0.5">
                        <li>Click <strong>Run Migrations</strong> (only if you uploaded new migration files).</li>
                        <li>Click <strong>Clear ALL Caches</strong>.</li>
                        <li>Hard-refresh the admin in your browser (<kbd>Ctrl</kbd>+<kbd>Shift</kbd>+<kbd>R</kbd>).</li>
                    </ol>
                </div>
            </div>

            @if ($this->lastResult)
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-900 dark:bg-black p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs uppercase tracking-wide text-gray-400 font-semibold">Last result</span>
                        <button
                            type="button"
                            class="text-xs text-gray-400 hover:text-white"
                            wire:click="$set('lastResult', null)"
                        >Clear</button>
                    </div>
                    <pre class="text-xs text-green-300 font-mono whitespace-pre-wrap break-all">{{ $this->lastResult }}</pre>
                </div>
            @endif
        </div>

        <div class="space-y-4">
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Server info</h3>
                <dl class="text-sm space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">PHP</dt>
                        <dd class="font-mono">{{ $phpVersion }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Laravel</dt>
                        <dd class="font-mono">{{ $laravelVersion }}</dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-gray-500">OPcache</dt>
                        <dd>
                            @if ($opcacheAvailable)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                    available
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                    not loaded
                                </span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 text-sm">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Still not seeing changes?</h3>
                <p class="text-gray-600 dark:text-gray-300 mb-3">
                    If buttons above don't help, the host's OPcache may need a PHP-FPM restart.
                </p>
                <ul class="text-xs text-gray-600 dark:text-gray-300 list-disc list-inside space-y-1">
                    <li>Open <strong>cPanel → Select PHP Version</strong> and re-save.</li>
                    <li>Or edit <code>.env</code> (add a space, save) — this sometimes triggers a worker reload.</li>
                    <li>Or rename <code>bootstrap/cache</code> and back to force autoload reload.</li>
                </ul>
            </div>
        </div>
    </div>
</x-filament-panels::page>
