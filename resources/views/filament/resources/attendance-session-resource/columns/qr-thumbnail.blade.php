<a href="{{ route('attendance.qr-image', $getRecord()->token) }}"
   target="_blank"
   rel="noopener"
   title="Open full-size QR in a new tab"
   class="inline-block transition hover:scale-105">
    <img src="{{ route('attendance.qr-image', $getRecord()->token) }}"
         alt="QR for {{ $getRecord()->name }}"
         class="w-16 h-16 rounded border border-gray-200 dark:border-gray-700 bg-white p-0.5"
         loading="lazy">
</a>
