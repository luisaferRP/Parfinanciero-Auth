@props(['disabled' => false])

<!-- <input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) !!}> -->
<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-b-2 border-gray-300 focus:border-[#008080] focus:ring-0 outline-none rounded-md shadow-sm  border-t-0 border-l-0 border-r-0']) !!}>

