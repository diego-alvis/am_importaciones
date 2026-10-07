<x-app-layout>
    <div class="flex items-center justify-center h-[calc(100vh-4rem)] bg-gray-100">
        <div class="bg-white p-10 rounded-2xl shadow-2xl max-w-md w-full border-t-8 border-cyan-500">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-black text-blue-900 mb-2">Apertura de Caja</h2>
                <p class="text-gray-500 font-bold uppercase tracking-widest text-sm">Sede: {{ Auth::user()->branch }}</p>
                <p class="text-gray-400 mt-2 text-sm">Identifíquese y seleccione su módulo, señor.</p>
            </div>
            
            <form action="{{ route('pos.setStand') }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label class="block text-sm font-bold text-blue-900 mb-2">Su Nombre y Apellido:</label>
                    <input type="text" name="vendedor_nombre" required placeholder="Ej. Carlos Pérez" class="w-full rounded-lg border-gray-300 focus:ring-cyan-500 focus:border-cyan-500 font-bold text-gray-800">
                </div>

                <div class="grid grid-cols-1 gap-4">
                    @foreach($stands as $stand)
                    <button type="submit" name="stand" value="{{ $stand }}" class="bg-gray-50 hover:bg-cyan-50 border-2 border-gray-200 hover:border-cyan-500 text-blue-900 font-black py-4 rounded-xl transition shadow-sm transform hover:scale-105 text-lg">
                        MÓDULO {{ $stand }}
                    </button>
                    @endforeach
                </div>
            </form>
        </div>
    </div>
</x-app-layout>