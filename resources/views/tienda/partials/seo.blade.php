@php
    $seoTitle = $seoTitle ?? 'CDA System';
    $seoDescription = $seoDescription ?? 'Tienda de tecnología en Ecuador: computadoras, repuestos, impresoras, accesorios y soporte técnico con garantía.';
    $seoCanonical = $seoCanonical ?? url()->current();
    $seoImage = $seoImage ?? null;
    $seoType = $seoType ?? 'website';
    $seoRobots = $seoRobots ?? 'index, follow';
    $seoJsonLd = $seoJsonLd ?? [];

    if (isset($seoJsonLd['@context'])) {
        $seoJsonLd = [$seoJsonLd];
    }

    $seoJsonLd = array_values(array_filter($seoJsonLd));
    $seoDescripcionCorta = \Illuminate\Support\Str::limit(strip_tags((string) $seoDescription), 160);
    $seoDescripcionSocial = \Illuminate\Support\Str::limit(strip_tags((string) $seoDescription), 200);
@endphp

<title>{{ $seoTitle }}</title>
<meta name="description" content="{{ $seoDescripcionCorta }}">
<meta name="robots" content="{{ $seoRobots }}">
<link rel="canonical" href="{{ $seoCanonical }}">

<meta property="og:site_name" content="CDA System">
<meta property="og:locale" content="es_EC">
<meta property="og:type" content="{{ $seoType }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescripcionSocial }}">
<meta property="og:url" content="{{ $seoCanonical }}">
@if($seoImage)
    <meta property="og:image" content="{{ $seoImage }}">
@endif

<meta name="twitter:card" content="{{ $seoImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescripcionSocial }}">
@if($seoImage)
    <meta name="twitter:image" content="{{ $seoImage }}">
@endif

@foreach($seoJsonLd as $schema)
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endforeach
