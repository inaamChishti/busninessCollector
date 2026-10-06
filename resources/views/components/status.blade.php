@props(['value'])@php $safe=strtolower(preg_replace('/[^a-z0-9]+/','-',(string)$value)); @endphp<span class="badge badge-{{ $safe }}">{{ str_replace('_',' ',ucfirst((string)$value)) }}</span>
