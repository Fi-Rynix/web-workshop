<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page {
            size: 210mm 165mm;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: sans-serif;
            width: 210mm;
            height: 165mm;
        }

        .label-wrapper {
            position: relative;
            width: 210mm;
            height: 165mm;
        }

        .label {
            position: absolute;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            box-sizing: border-box;
        }

        .nama-barang {
            font-size: 10px;
            margin-bottom: 2px;
            font-weight: 500;
            line-height: 1.2;
            word-wrap: break-word;
        }

        .harga {
            font-size: 13px;
            font-weight: bold;
            color: #d32f2f;
        }
    </style>
</head>
<body>

@php
    $labelWidth = $config['labelWidth'];
    $labelHeight = $config['labelHeight'];
    $gapX = $config['gapX'];
    $gapY = $config['gapY'];
    $cols = $config['cols'];
    $rows = $config['rows'];
    $marginLeft = $config['marginLeft'];
    $marginTop = $config['marginTop'];
@endphp

<div class="label-wrapper">
    @for ($row = 0; $row < $rows; $row++)
        @for ($col = 0; $col < $cols; $col++)
            @php
                $index = ($row * $cols) + $col;
                $left = $marginLeft + ($col * ($labelWidth + $gapX));
                $top = $marginTop + ($row * ($labelHeight + $gapY));
            @endphp
            @if(isset($labels[$index]) && $labels[$index])
            <div class="label" style="width: {{ $labelWidth }}mm; height: {{ $labelHeight }}mm; left: {{ $left }}mm; top: {{ $top }}mm;">
                <img src="data:image/png;base64,{{ $labels[$index]['barcode'] }}" style="max-width: 24mm; height: 6mm; margin-bottom: 1px;">
                <div class="nama-barang">{{ $labels[$index]['nama_barang'] }}</div>
                <div class="harga">{{ $labels[$index]['harga'] }}</div>
            </div>
            @endif
        @endfor
    @endfor
</div>

</body>
</html>
