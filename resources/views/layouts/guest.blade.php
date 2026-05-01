<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Workshop Pro' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Noto+Sans+Arabic:wght@400;600;700;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-950 font-sans antialiased">
    {{ $slot }}
</body>
</html>
