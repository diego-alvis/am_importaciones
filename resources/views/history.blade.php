<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-blue-900 leading-tight">
            {{ $dateParam ? 'Detalle de Arqueo: ' . \Carbon\Carbon::parse($dateParam)->format('d/m/Y') : 'Directorio de Historial Financiero' }}
        </h2>
    </x-slot>

    <div class="py-8 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(!$dateParam)
                <!-- VISTA 1: LISTA DE REPORTES -->
                <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-200">
                    <div class="p-6">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="text-xs text-white uppercase bg-blue-900">
                                <tr>
                                    <th class="px-4 py-4 rounded-tl-lg">Fecha del Arqueo</th>
                                    <th class="px-4 py-4 text-center">Sede</th>
                                    <th class="px-4 py-4 text-center">Módulo / Stand</th>
                                    <th class="px-4 py-4">Vendedor Responsable</th>
                                    <th class="px-4 py-4 text-center">Operaciones</th>
                                    <th class="px-4 py-4 text-center rounded-tr-lg">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sessions as $session)
                                <tr class="border-b hover:bg-blue-50 transition">
                                    <td class="px-4 py-4 font-black text-gray-900">{{ \Carbon\Carbon::parse($session['date'])->format('d/m/Y') }}</td>
                                    <td class="px-4 py-4 text-center font-bold">{{ $session['sede'] }}</td>
                                    <td class="px-4 py-4 text-center">{{ $session['stand'] }}</td>
                                    <td class="px-4 py-4 uppercase text-xs font-bold">{{ $session['vendedor'] }}</td>
                                    <td class="px-4 py-4 text-center">{{ $session['total_sales'] }} ventas</td>
                                    <td class="px-4 py-4 text-center">
                                        <a href="{{ route('history', ['date' => $session['date'], 'sede' => $session['sede'], 'stand' => $session['stand']]) }}" 
                                           class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded font-bold text-xs shadow transition">
                                           Ver Reporte
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500 italic">No hay registros financieros pasados, señor.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <!-- VISTA 2: REPORTE DETALLADO -->
                <div class="mb-6">
                    <a href="{{ route('history') }}" class="bg-gray-800 hover:bg-gray-700 text-white px-5 py-2 rounded-lg font-bold shadow transition">
                        ← Volver a la Lista
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                    <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-blue-900"><p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total Bruto</p><p class="text-2xl font-black text-gray-800 mt-1">S/ {{ number_format($totalBruto, 2) }}</p></div>
                    <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-purple-600"><p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total Yape/Plin</p><p class="text-2xl font-black text-gray-800 mt-1">S/ {{ number_format($totalYape, 2) }}</p></div>
                    <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-cyan-500"><p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Ventas Hidrogel</p><p class="text-2xl font-black text-gray-800 mt-1">S/ {{ number_format($totalHidrogel, 2) }}</p></div>
                    <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-red-500 opacity-{{ Auth::user()->role === 'admin' ? '50' : '100' }}"><p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Viáticos y Bonos</p><p class="text-2xl font-black text-red-600 mt-1">- S/ {{ number_format($gastos, 2) }}</p></div>
                    <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-green-500"><p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Efectivo en Caja</p><p class="text-2xl font-black text-green-600 mt-1">S/ {{ number_format($restanteCaja, 2) }}</p></div>
                </div>

                <div class="text-center mb-6">
                    <button onclick="document.getElementById('detalleArticulos').classList.toggle('hidden')" class="bg-blue-900 hover:bg-cyan-500 text-white px-8 py-3 rounded-xl font-black shadow-lg transition transform hover:scale-105">
                        Abrir Reporte Específico de Artículos Vendidos
                    </button>
                </div>

                <div id="detalleArticulos" class="hidden bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-cyan-600 mb-4 border-b border-gray-200 pb-3">Desglose de Artículos de la Sesión</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-600">
                                <thead class="text-xs text-white uppercase bg-cyan-600">
                                    <tr>
                                        <th class="px-4 py-3 rounded-tl-lg">Hora</th>
                                        <th class="px-4 py-3">Accesorio Vendido</th>
                                        <th class="px-4 py-3 text-center">Cant.</th>
                                        <th class="px-4 py-3 text-center">Precio Acordado</th>
                                        <th class="px-4 py-3 text-center">Método</th>
                                        <th class="px-4 py-3 text-right rounded-tr-lg">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ventasHistorial as $index => $venta)
                                        @php 
                                            $items = is_string($venta->productos) ? json_decode($venta->productos, true) : ($venta->productos ?? []);
                                            $bgColor = $index % 2 === 0 ? 'bg-white' : 'bg-slate-50';
                                            $totalItems = count($items);
                                        @endphp
                                        
                                        @foreach($items as $itemIndex => $item)
                                        <tr class="{{ $bgColor }} hover:bg-cyan-50 transition border-x border-gray-100 {{ $itemIndex === $totalItems - 1 ? 'border-b-2 border-gray-300' : 'border-b border-gray-100' }}">
                                            <td class="px-4 py-3 font-bold text-gray-500">{{ \Carbon\Carbon::parse($venta->fecha)->format('H:i') }}</td>
                                            <td class="px-4 py-3 font-bold text-gray-800">{{ $item['name'] ?? 'Producto' }}</td>
                                            <td class="px-4 py-3 text-center font-black">{{ $item['qty'] ?? 1 }}</td>
                                            <td class="px-4 py-3 text-center text-blue-600 font-bold">S/ {{ number_format($item['price'] ?? 0, 2) }}</td>
                                            <td class="px-4 py-3 text-center text-xs font-bold uppercase {{ $venta->metodo_pago == 'yape' ? 'text-purple-600' : 'text-green-600' }}">{{ $venta->metodo_pago }}</td>
                                            <td class="px-4 py-3 text-right font-black text-gray-900">S/ {{ number_format(($item['price'] ?? 0) * ($item['qty'] ?? 1), 2) }}</td>
                                        </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>