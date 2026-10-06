<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>@yield('title', 'Business Email Collector') · {{ config('app.name') }}</title><link rel="stylesheet" href="{{ asset('assets/css/app.css') }}"></head>
<body><div class="app-shell">
<aside class="sidebar" id="sidebar"><a class="brand" href="{{ route('dashboard') }}"><span class="brand-mark">BC</span><span>Business Collector</span></a><nav>
@php $nav=[['dashboard','Dashboard','▦'],['locations.index','Locations','⌖'],['businesses.index','Businesses','▤'],['categories.index','Categories','⌘'],['emails.index','Emails','@'],['reports.index','Reports','📊'],['jobs.index','Jobs','↻'],['failed-jobs.index','Failed Jobs','!'],['imports.index','Imports','⇧'],['exports.index','Exports','⇩'],['duplicates.index','Duplicates','≋'],['activity.index','Activity Log','◷']]; @endphp
@foreach($nav as $item)@php [$route,$label,$icon]=$item; @endphp<a href="{{ route($route) }}" class="{{ request()->routeIs(str_replace('.index','.*',$route))||request()->routeIs($route)?'active':'' }}"><span>{{ $icon }}</span>{{ $label }}</a>@endforeach
{{-- Wikidata sub-menu --}}
<style>.wd-sub{padding-left:1.25rem;display:none;flex-direction:column}.wd-sub a{font-size:.82rem!important;padding:.35rem .75rem!important}.wd-parent.open+.wd-sub{display:flex}</style>
<a href="{{ route('wikidata.index') }}" class="wd-parent {{ request()->routeIs('wikidata.*')?'active':'' }}" onclick="this.classList.toggle('open');return true"><span>🌐</span>Wikidata API <span style="margin-left:auto;font-size:.7rem">{{ request()->routeIs('wikidata.*')?'▲':'▼' }}</span></a>
<div class="wd-sub {{ request()->routeIs('wikidata.*')?'':'hidden' }}" id="wd-sub" style="{{ request()->routeIs('wikidata.*')?'display:flex':'' }}">
<a href="{{ route('wikidata.index') }}" class="{{ request()->routeIs('wikidata.index')||request()->routeIs('wikidata.fetch')||request()->routeIs('wikidata.store')?'active':'' }}">🔍 Search API</a>
<a href="{{ route('wikidata.saved') }}" class="{{ request()->routeIs('wikidata.saved')?'active':'' }}">💾 Saved Businesses</a>
<a href="{{ route('wikidata.emails') }}" class="{{ request()->routeIs('wikidata.emails*')?'active':'' }}">📧 Get Emails</a>
</div>
@php $nav2=[['settings.index','Settings','⚙'],['profile.edit','Profile','◉']]; @endphp
@foreach($nav2 as $item)@php [$route,$label,$icon]=$item; @endphp<a href="{{ route($route) }}" class="{{ request()->routeIs(str_replace('.index','.*',$route))||request()->routeIs($route)?'active':'' }}"><span>{{ $icon }}</span>{{ $label }}</a>@endforeach
</nav><form method="post" action="{{ route('logout') }}" class="logout">@csrf<button class="nav-button">↪ Sign out</button></form></aside>
<main><header class="topbar"><button class="menu" type="button" data-menu>☰</button><div><h1>@yield('title','Dashboard')</h1><p>@yield('subtitle','Public business data collection and verification')</p></div><div class="user-chip">{{ auth()->user()->name ?? '' }}</div></header><section class="content">
@if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert danger"><strong>Please correct the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')</section></main></div><script src="{{ asset('assets/js/app.js') }}"></script>@stack('scripts')</body></html>
