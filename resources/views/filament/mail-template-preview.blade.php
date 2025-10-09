<div class="p-4">
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <h3 class="text-lg font-semibold mb-2">Asunto:</h3>
        <p class="mb-4 text-gray-700 dark:text-gray-300">{{ $record->subject }}</p>

        <h3 class="text-lg font-semibold mb-2">Contenido HTML:</h3>
        <div class="bg-white dark:bg-gray-900 p-4 rounded border border-gray-300 dark:border-gray-700 mb-4">
            {!! $record->html_template !!}
        </div>

        @if($record->text_template)
            <h3 class="text-lg font-semibold mb-2">Contenido Texto:</h3>
            <pre class="bg-white dark:bg-gray-900 p-4 rounded border border-gray-300 dark:border-gray-700 text-sm whitespace-pre-wrap">{{ $record->text_template }}</pre>
        @endif
    </div>
</div>
