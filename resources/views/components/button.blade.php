<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#008080] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#007373] focus:bg-[#007373] active:bg-[#006666] focus:outline-none focus:ring-2 focus:ring-[#008080] focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>



