<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <h2 class="font-extrabold text-2xl text-blue-900 leading-tight flex items-center">
                <svg class="w-8 h-8 mr-2 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                {{ Auth::user()->role === 'admin' ? 'Auditoría Global - A&M Importaciones' : 'Mi Arqueo Diario - Módulo ' . session('stand_asignado') }}
            </h2>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <a href="{{ route('inventory.index') }}" class="bg-cyan-500 hover:bg-cyan-600 text-white px-5 py-2 rounded-lg font-bold shadow-md transition">Inventario Global</a>
                <a href="{{ route('pos.index') }}" class="bg-blue-900 hover:bg-blue-800 text-white px-5 py-2 rounded-lg font-bold shadow-md transition">POS Vendedor</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Buscador Jerárquico (Solo Admin) -->
            @if(Auth::user()->role === 'admin')
            <div class="mb-6 bg-white p-5 rounded-xl shadow-sm border border-gray-200 flex flex-col md:flex-row gap-4 items-end">
                <form id="filterForm" method="GET" action="{{ route('dashboard') }}" class="flex flex-col md:flex-row gap-4 w-full items-end">
                    <div class="flex-1 w-full">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Filtrar por Sede</label>
                        <select name="sede" onchange="document.getElementById('standSelect').value=''; this.form.submit()" class="w-full rounded-lg border-gray-300 font-bold text-blue-900 focus:ring-cyan-500 focus:border-cyan-500">
                            <option value="">Todas las Sedes</option>
                            @foreach($sedesDisponibles as $sede)
                                <option value="{{ $sede }}" {{ $sedeFiltro == $sede ? 'selected' : '' }}>{{ $sede }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 w-full">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Filtrar por Tienda / Stand</label>
                        <select name="stand" id="standSelect" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 font-bold text-blue-900 focus:ring-cyan-500 focus:border-cyan-500" {{ !$sedeFiltro ? 'disabled' : '' }}>
                            <option value="">Todos los Stands</option>
                            @foreach($standsDisponibles as $stand)
                                <option value="{{ $stand }}" {{ $standFiltro == $stand ? 'selected' : '' }}>{{ $stand }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <a href="{{ route('dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2.5 rounded-lg font-bold transition block text-center">Limpiar</a>
                    </div>
                </form>
            </div>
            @endif

            <!-- Panel de Métricas (5 Columnas) -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
                <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-blue-900">
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total Bruto</p>
                    <p class="text-2xl font-black text-gray-800 mt-1">S/ {{ number_format($totalBruto, 2) }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-purple-600">
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total Yape/Plin</p>
                    <p class="text-2xl font-black text-gray-800 mt-1">S/ {{ number_format($totalYape, 2) }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-cyan-500">
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Ventas Hidrogel</p>
                    <p class="text-2xl font-black text-gray-800 mt-1">S/ {{ number_format($totalHidrogel, 2) }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-red-500 opacity-{{ Auth::user()->role === 'admin' ? '50' : '100' }}">
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Viáticos y Bonos</p>
                    <p class="text-2xl font-black text-red-600 mt-1">- S/ {{ number_format($gastos, 2) }}</p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-green-500">
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Efectivo en Caja</p>
                    <p class="text-2xl font-black text-green-600 mt-1">S/ {{ number_format($restanteCaja, 2) }}</p>
                </div>
            </div>

            <!-- Tabla 1: Registro General de Ventas -->
            <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-200 mb-8">
                <div class="p-6">
                    <h3 class="text-xl font-bold text-blue-900 mb-4 border-b border-gray-200 pb-3">Registro de Ventas (Filtro Actual)</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="text-xs text-white uppercase bg-blue-900">
                                <tr>
                                    <th class="px-4 py-4 rounded-tl-lg">Hora</th>
                                    <th class="px-4 py-4">Sede / Stand</th>
                                    <th class="px-4 py-4">Vendedor</th>
                                    <th class="px-4 py-4 text-center">Método</th>
                                    <th class="px-4 py-4 text-right rounded-tr-lg">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ventasHoy as $venta)
                                <tr class="border-b hover:bg-blue-50 transition">
                                    <td class="px-4 py-4 font-bold text-gray-900">{{ \Carbon\Carbon::parse($venta->fecha)->format('H:i') }}</td>
                                    <td class="px-4 py-4 font-semibold">{{ $venta->sede }} - {{ $venta->stand }}</td>
                                    <td class="px-4 py-4 text-gray-700">{{ $venta->vendedor_name }}</td>
                                    <td class="px-4 py-4 text-center uppercase font-bold {{ $venta->metodo_pago == 'yape' ? 'text-purple-600' : 'text-green-600' }}">
                                        {{ $venta->metodo_pago }}
                                    </td>
                                    <td class="px-4 py-4 text-right font-black text-gray-800">S/ {{ number_format($venta->total, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500 italic">No se han registrado ventas con estos filtros.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tabla 2: Reporte Específico de Productos -->
            <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-200">
                <div class="p-6">
                    <h3 class="text-xl font-bold text-cyan-600 mb-4 border-b border-gray-200 pb-3">Detalle Específico de Artículos Vendidos</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="text-xs text-white uppercase bg-cyan-600">
                                <tr>
                                    <th class="px-4 py-3 rounded-tl-lg">Hora</th>
                                    <th class="px-4 py-3">Sede / Stand</th>
                                    <th class="px-4 py-3">Accesorio Vendido</th>
                                    <th class="px-4 py-3 text-center">Cant.</th>
                                    <th class="px-4 py-3 text-center">Precio Acordado</th>
                                    <th class="px-4 py-3 text-center">Método</th>
                                    <th class="px-4 py-3 text-right rounded-tr-lg">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ventasHoy as $venta)
                                    @php
                                        $items = is_string($venta->productos) ? json_decode($venta->productos, true) : ($venta->productos ?? []);
                                    @endphp
                                    @foreach($items as $item)
                                    <tr class="border-b hover:bg-cyan-50 transition">
                                        <td class="px-4 py-3 font-bold">{{ \Carbon\Carbon::parse($venta->fecha)->format('H:i') }}</td>
                                        <td class="px-4 py-3">{{ $venta->sede }} - {{ $venta->stand }}</td>
                                        <td class="px-4 py-3 font-bold text-gray-800">{{ $item['name'] ?? 'Producto' }}</td>
                                        <td class="px-4 py-3 text-center font-black">{{ $item['qty'] ?? 1 }}</td>
                                        <td class="px-4 py-3 text-center text-blue-600 font-bold">S/ {{ number_format($item['price'] ?? 0, 2) }}</td>
                                        <td class="px-4 py-3 text-center text-xs font-bold uppercase {{ $venta->metodo_pago == 'yape' ? 'text-purple-600' : 'text-green-600' }}">{{ $venta->metodo_pago }}</td>
                                        <td class="px-4 py-3 text-right font-black text-gray-900">S/ {{ number_format(($item['price'] ?? 0) * ($item['qty'] ?? 1), 2) }}</td>
                                    </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-gray-500 italic">No hay detalle de artículos para mostrar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>