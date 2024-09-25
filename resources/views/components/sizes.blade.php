@php
    $headers = ['欧码EU' => 'EU', 'CHN中国码' => 'MM', 'US美码' => 'US', 'UK英码' => 'UK'];
    $sizes = collect($sizeData)
        ->whereIn('sizeKey', array_keys($headers)) // Отфильтровываем только нужные колонки
        ->mapWithKeys(function ($item) use ($headers) {
            return [$headers[$item['sizeKey']] => explode(',', $item['sizeValue'])];
        });
    $rows = $sizes->map(function ($sizeArray) {
    return count($sizeArray);
})->max(); // Определяем количество рядов
@endphp

<table border="1">
    <thead>
    <tr>
        @foreach ($sizes->keys() as $key)
            <th>{{ $key }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @for ($i = 0; $i < $rows; $i++)
        <tr>
            @foreach ($sizes as $sizeArray)
                <td>{{ $sizeArray[$i] ?? '/' }}</td>
            @endforeach
        </tr>
    @endfor
    </tbody>
</table>
