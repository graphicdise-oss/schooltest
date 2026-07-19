@php
    $sarabunWeights = [
        300 => 'Sarabun-Light.ttf',
        400 => 'Sarabun-Regular.ttf',
        500 => 'Sarabun-Medium.ttf',
        600 => 'Sarabun-SemiBold.ttf',
        700 => 'Sarabun-Bold.ttf',
        800 => 'Sarabun-ExtraBold.ttf',
        900 => 'Sarabun-ExtraBold.ttf',
    ];
@endphp
@foreach($sarabunWeights as $weight => $file)
@font-face {
    font-family: 'Sarabun';
    src: url('{{ 'file://' . public_path('fonts/' . $file) }}') format('truetype');
    font-weight: {{ $weight }};
}
@endforeach
@font-face {
    font-family: 'Sarabun';
    src: url('{{ 'file://' . public_path('fonts/Sarabun-Bold.ttf') }}') format('truetype');
    font-weight: bold;
}
@font-face {
    font-family: 'Sarabun';
    src: url('{{ 'file://' . public_path('fonts/Sarabun-Regular.ttf') }}') format('truetype');
    font-weight: normal;
}
