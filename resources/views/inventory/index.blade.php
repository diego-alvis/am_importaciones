<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-extrabold text-2xl text-blue-900 leading-tight">
                {{ __('Inventario') }} {{ Auth::user()->role === 'admin' ? 'Global' : '- Sede ' . Auth::user()->branch }}
            </h2>
            @if(Auth::user()->role === 'admin')
            <a href="{{ route('inventory.create') }}" class="bg-cyan-500 hover:bg-cyan-600 text-white px-5 py-2 rounded-lg font-bold shadow-md transition duration-300">
                + Nuevo Producto
            </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Motor de Búsqueda -->
            <div class="bg-white p-4 rounded-xl shadow-md mb-6 flex space-x-4">
                <input type="text" id="searchInput" onkeyup="filterInventory()" placeholder="Buscar por nombre o SKU..." class="flex-grow rounded-lg border-gray-300 focus:border-cyan-500 focus:ring-cyan-500">
                
                <select id="categoryFilter" onchange="filterInventory()" class="rounded-lg border-gray-300 focus:border-cyan-500 focus:ring-cyan-500 bg-gray-50 font-bold text-gray-700">
                    <option value="ALL">Todas las Categorías</option>
                    @php $categorias = $products->pluck('category')->unique(); @endphp
                    @foreach($categorias as $cat)
                        <option value="{{ strtoupper($cat) }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tabla de Inventario -->
            <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600" id="inventoryTable">
                        <thead class="text-xs text-white uppercase bg-blue-900">
                            <tr>
                                <th class="px-4 py-4 rounded-tl-lg">SKU / Accesorio</th>
                                <th class="px-4 py-4 text-center">Categoría</th>
                                @if(Auth::user()->role === 'admin' || Auth::user()->branch === 'Plaza Norte')
                                <th class="px-4 py-4 text-center bg-blue-800">Plaza Norte</th>
                                @endif
                                @if(Auth::user()->role === 'admin' || Auth::user()->branch === 'La Muela')
                                <th class="px-4 py-4 text-center bg-blue-700">La Muela</th>
                                @endif
                                @if(Auth::user()->role === 'admin' || Auth::user()->branch === 'Av. 54')
                                <th class="px-4 py-4 text-center bg-blue-600">Av. 54</th>
                                @endif
                                <th class="px-4 py-4 text-right rounded-tr-lg">Precio Venta</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr class="border-b hover:bg-blue-50 transition inventory-row" data-name="{{ strtolower($product->sku . ' ' . $product->name) }}" data-category="{{ strtoupper($product->category) }}">
                                    <td class="px-4 py-4 font-bold text-gray-900">{{ $product->sku ?? 'N/A' }} - {{ $product->name }}</td>
                                    <td class="px-4 py-4 text-center uppercase text-xs">{{ $product->category }}</td>
                                    @if(Auth::user()->role === 'admin' || Auth::user()->branch === 'Plaza Norte')
                                    <td class="px-4 py-4 text-center font-black text-gray-800">{{ $product->stock_plaza_norte ?? 0 }}</td>
                                    @endif
                                    @if(Auth::user()->role === 'admin' || Auth::user()->branch === 'La Muela')
                                    <td class="px-4 py-4 text-center font-black text-gray-800">{{ $product->stock_la_muela ?? 0 }}</td>
                                    @endif
                                    @if(Auth::user()->role === 'admin' || Auth::user()->branch === 'Av. 54')
                                    <td class="px-4 py-4 text-center font-black text-gray-800">{{ $product->stock_av_54 ?? 0 }}</td>
                                    @endif
                                    <td class="px-4 py-4 text-right text-green-600 font-black text-lg">S/ {{ number_format($product->sale_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function filterInventory() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let cat = document.getElementById('categoryFilter').value;
            let rows = document.querySelectorAll('.inventory-row');
            
            rows.forEach(row => {
                let textMatch = row.dataset.name.includes(input);
                let catMatch = (cat === 'ALL' || row.dataset.category === cat);
                row.style.display = (textMatch && catMatch) ? '' : 'none';
            });
        }
    </script>
</x-app-layout> 