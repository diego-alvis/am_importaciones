<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-blue-900 leading-tight">
            {{ __('Ingreso de Nueva Mercadería') }}
        </h2>
    </x-slot>

    <div class="py-8 bg-gray-100 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-200 p-6">
                <form action="{{ route('inventory.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Datos Generales -->
                        <div>
                            <label class="block text-sm font-bold text-blue-900 mb-1">Código SKU</label>
                            <input type="text" name="sku" required placeholder="Ej. AUD-F9-01" class="w-full rounded-lg border-gray-300 focus:ring-cyan-500 focus:border-cyan-500">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-blue-900 mb-1">Nombre del Accesorio</label>
                            <input type="text" name="name" required placeholder="Ej. Audífonos F9 Pro" class="w-full rounded-lg border-gray-300 focus:ring-cyan-500 focus:border-cyan-500">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-blue-900 mb-1">Categoría</label>
                            <select name="category" class="w-full rounded-lg border-gray-300 focus:ring-cyan-500 focus:border-cyan-500">
                                <option value="Audífonos">Audífonos</option>
                                <option value="Cables">Cables</option>
                                <option value="Fundas">Fundas</option>
                                <option value="Micas">Micas</option>
                                <option value="Cargadores">Cargadores</option>
                                <option value="Hidrogel">Hidrogel</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-blue-900 mb-1">Precio de Venta (S/)</label>
                            <input type="number" step="0.01" name="sale_price" required placeholder="0.00" class="w-full rounded-lg border-gray-300 focus:ring-cyan-500 focus:border-cyan-500">
                        </div>
                    </div>

                    <hr class="my-6 border-gray-200">
                    <h3 class="font-bold text-lg text-blue-900 mb-4">Stock Inicial por Sede</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-bold text-blue-900 mb-1">Plaza Norte</label>
                            <input type="number" name="stock_plaza_norte" value="0" class="w-full rounded-lg border-gray-300 focus:ring-cyan-500 focus:border-cyan-500">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-blue-900 mb-1">La Muela</label>
                            <input type="number" name="stock_la_muela" value="0" class="w-full rounded-lg border-gray-300 focus:ring-cyan-500 focus:border-cyan-500">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-blue-900 mb-1">Av. 54</label>
                            <input type="number" name="stock_av_54" value="0" class="w-full rounded-lg border-gray-300 focus:ring-cyan-500 focus:border-cyan-500">
                        </div>
                    </div>

                    <div class="flex justify-end mt-8">
                        <a href="{{ route('inventory.index') }}" class="mr-4 text-gray-600 hover:text-gray-900 font-bold py-2 px-4">Cancelar</a>
                        <button type="submit" class="bg-blue-900 hover:bg-blue-800 text-white font-bold py-2 px-6 rounded-lg shadow-lg">
                            Guardar Accesorio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>