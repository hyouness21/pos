@props(['value' => '', 'name' => 'phone', 'required' => false])

@php
$countries = [
    ['+961','🇱🇧','Lebanon'],
    ['+213','🇩🇿','Algeria'],
    ['+1','🇺🇸','USA / Canada'],
    ['+44','🇬🇧','UK'],
    ['+33','🇫🇷','France'],
    ['+49','🇩🇪','Germany'],
    ['+34','🇪🇸','Spain'],
    ['+39','🇮🇹','Italy'],
    ['+212','🇲🇦','Morocco'],
    ['+216','🇹🇳','Tunisia'],
    ['+218','🇱🇾','Libya'],
    ['+20','🇪🇬','Egypt'],
    ['+221','🇸🇳','Senegal'],
    ['+966','🇸🇦','Saudi Arabia'],
    ['+971','🇦🇪','UAE'],
    ['+974','🇶🇦','Qatar'],
    ['+965','🇰🇼','Kuwait'],
    ['+962','🇯🇴','Jordan'],
    ['+963','🇸🇾','Syria'],
    ['+964','🇮🇶','Iraq'],
    ['+90','🇹🇷','Turkey'],
    ['+98','🇮🇷','Iran'],
    ['+92','🇵🇰','Pakistan'],
    ['+91','🇮🇳','India'],
    ['+86','🇨🇳','China'],
    ['+55','🇧🇷','Brazil'],
    ['+27','🇿🇦','South Africa'],
    ['+234','🇳🇬','Nigeria'],
];

$existingCode = '+961';
$existingNumber = $value ?? '';
foreach ($countries as [$code, $flag, $label]) {
    if (str_starts_with($existingNumber, $code)) {
        $existingCode   = $code;
        $existingNumber = ltrim(substr($existingNumber, strlen($code)));
        break;
    }
}

$countriesJson = json_encode(array_map(fn($c) => ['code'=>$c[0],'flag'=>$c[1],'name'=>$c[2]], $countries));
@endphp

<div x-data="{
    open: false,
    code: '{{ $existingCode }}',
    number: '{{ $existingNumber }}',
    countries: {{ $countriesJson }},
    selected() { return this.countries.find(c => c.code === this.code) || this.countries[0]; },
    pick(c) { this.code = c.code; this.open = false; }
}" @click.outside="open = false">
    <div class="flex gap-2">

        {{-- Trigger: shows only flag + code --}}
        <div class="relative shrink-0">
            <button type="button" @click="open = !open"
                    class="flex items-center gap-1 border border-gray-300 rounded-xl px-3 py-2.5 bg-white text-sm focus:ring-2 focus:ring-indigo-500 outline-none whitespace-nowrap">
                <span x-text="selected().flag" class="text-base"></span>
                <span x-text="selected().code" class="text-gray-700 font-medium"></span>
                <svg class="w-3.5 h-3.5 text-gray-400 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            {{-- Dropdown: shows flag + name + code --}}
            <div x-show="open" x-cloak
                 class="absolute z-50 top-full left-0 mt-1 w-56 bg-white border border-gray-200 rounded-xl shadow-lg overflow-y-auto overscroll-y-contain" style="max-height:185px">
                <template x-for="c in countries" :key="c.code">
                    <button type="button" @click="pick(c)"
                            class="w-full flex items-center gap-2 px-3 py-2 text-sm hover:bg-indigo-50 text-left"
                            :class="c.code === code ? 'bg-indigo-50 font-semibold text-indigo-700' : 'text-gray-700'">
                        <span x-text="c.flag" class="text-base shrink-0"></span>
                        <span x-text="c.name" class="flex-1 truncate"></span>
                        <span x-text="c.code" class="text-gray-400 text-xs shrink-0"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- Phone number input --}}
        <input type="tel" x-model="number" placeholder="Phone number"
               {{ $required ? 'required' : '' }}
               class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
    </div>
    <input type="hidden" name="{{ $name }}" :value="number ? code + number : ''">
</div>
