<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - A&M Importaciones</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 h-screen overflow-hidden flex flex-col font-sans">
    
    <div class="bg-blue-900 text-white px-6 py-3 flex justify-between items-center shadow-md z-50">
        <h1 class="font-black text-xl tracking-wider">A&M IMPORTACIONES <span class="text-cyan-400 font-normal">| CAJA</span></h1>
        <div class="flex space-x-3 items-center">
            <a href="{{ route('inventory.index') }}" class="bg-blue-800 hover:bg-cyan-500 px-4 py-2 rounded shadow text-sm font-bold transition">Ver Inventario</a>
            <a href="{{ route('dashboard') }}" class="bg-blue-800 hover:bg-cyan-500 px-4 py-2 rounded shadow text-sm font-bold transition">Mi Arqueo Diario</a>
            <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                @csrf
                <button type="submit" class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded shadow text-sm font-bold transition">Cerrar Sesión</button>
            </form>
        </div>
    </div>

    <div class="flex flex-row w-full flex-grow h-full overflow-hidden">
        
        <!-- Panel Izquierdo: Catálogo Interactivo -->
        <div class="w-2/3 flex flex-col border-r border-gray-300 bg-gray-50 h-full">
            
            <!-- Buscador y Categorías -->
            <div class="p-4 bg-white border-b border-gray-200 shadow-sm z-10">
                <input type="text" id="posSearch" onkeyup="filterPos()" placeholder="Buscar producto..." class="w-full mb-3 rounded-lg border-gray-300 focus:border-cyan-500 focus:ring-cyan-500 font-bold text-gray-700">
                
                <div class="flex space-x-2 overflow-x-auto pb-2">
                    <button onclick="setPosCategory('ALL')" class="pos-cat-btn bg-cyan-500 text-white px-4 py-2 rounded-full text-sm font-bold shadow-sm whitespace-nowrap">Todos</button>
                    @php $categorias =$products->pluck('category')->unique(); @endphp
                    @foreach($categorias as $cat)
                        <button onclick="setPosCategory('{{ strtoupper($cat) }}')" class="pos-cat-btn bg-gray-200 hover:bg-cyan-100 text-gray-700 px-4 py-2 rounded-full text-sm font-bold shadow-sm whitespace-nowrap">{{ $cat }}</button>
                    @endforeach
                </div>
                <input type="hidden" id="activePosCategory" value="ALL">
            </div>

            <!-- Cuadrícula de Productos -->
            <div class="p-6 overflow-y-auto h-full content-start">
                 <div class="grid grid-cols-3 gap-5" id="posGrid">
                     @foreach ($products as $product)
                     <button onclick="addToCart('{{ $product->_id }}', '{{ addslashes($product->name) }}', {{ $product->sale_price }}, '{{ strtoupper($product->category) }}')" class="pos-item bg-white border border-gray-200 rounded-xl p-5 flex flex-col items-center shadow-sm hover:border-cyan-500 transition transform hover:scale-105" data-name="{{ strtolower($product->name) }}" data-category="{{ strtoupper($product->category) }}">
                        <span class="font-bold text-gray-800 text-sm text-center leading-tight line-clamp-2">{{ $product->name }}</span>
                        <span class="text-xs text-gray-400 mt-1 uppercase">{{ $product->category }}</span>
                        <span class="text-xl font-black text-green-600 mt-3">S/ {{ number_format($product->sale_price, 2) }}</span>
                     </button>
                     @endforeach
                 </div>
            </div>
        </div>

        <!-- Panel Derecho: Ticket -->
        <div class="w-1/3 bg-white flex flex-col h-full shadow-2xl z-10">
            <div class="p-4 bg-gray-100 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-bold text-blue-900 uppercase">Ticket Actual</h2>
                <span class="text-xs text-gray-500 font-bold bg-white px-2 py-1 rounded shadow-sm">Stand: {{ session('stand_asignado', 'Admin') }}</span>
            </div>
            
            <div id="cart-items" class="flex-grow p-4 overflow-y-auto"></div>

            <div class="p-6 bg-white border-t border-gray-200">
                <div class="flex justify-between items-end mb-6">
                    <span class="font-black text-gray-400 uppercase text-sm">Total a Cobrar</span>
                    <span id="cart-total" class="text-5xl font-black text-blue-900">S/ 0.00</span>
                </div>
                
                <input type="hidden" id="payment-method" value="efectivo">
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <button id="btn-efectivo" onclick="setPayment('efectivo')" class="border-2 border-green-500 bg-green-50 text-green-700 font-bold py-3 rounded-lg transition shadow-sm">EFECTIVO</button>
                    <button id="btn-yape" onclick="setPayment('yape')" class="border-2 border-purple-200 text-purple-700 font-bold py-3 rounded-lg hover:bg-purple-50 transition shadow-sm">YAPE/PLIN</button>
                </div>
                
                <button onclick="processSale()" class="w-full bg-blue-900 text-white font-black text-xl py-4 rounded-xl hover:bg-blue-800 shadow-lg transform hover:scale-[1.02] transition">
                    PROCESAR VENTA
                </button>
            </div>
        </div>
    </div>

    <!-- Motor JavaScript -->
    <script>
        // Filtros del POS
        function setPosCategory(cat) {
            document.getElementById('activePosCategory').value = cat;
            
            // Actualizar diseño de botones
            document.querySelectorAll('.pos-cat-btn').forEach(btn => {
                btn.classList.remove('bg-cyan-500', 'text-white');
                btn.classList.add('bg-gray-200', 'text-gray-700');
            });
            event.target.classList.remove('bg-gray-200', 'text-gray-700');
            event.target.classList.add('bg-cyan-500', 'text-white');
            
            filterPos();
        }

        function filterPos() {
            let input = document.getElementById('posSearch').value.toLowerCase();
            let cat = document.getElementById('activePosCategory').value;
            let items = document.querySelectorAll('.pos-item');
            
            items.forEach(item => {
                let textMatch = item.dataset.name.includes(input);
                let catMatch = (cat === 'ALL' || item.dataset.category === cat);
                item.style.display = (textMatch && catMatch) ? 'flex' : 'none';
            });
        }

        // Lógica del Carrito
        let cart = [];
        let total = 0;

        function addToCart(id, name, price, category) {
            let finalName = name;
            let uniqueId = id;

            // Interceptor para solicitar modelo de celular
            let requiresModel = ['FIBRAS DE VIDRIO', 'CASE', 'HIDROGEL'];
            if (category && requiresModel.includes(category)) {
                let modelo = prompt(`Especifique el modelo de celular para:\n${name}`);
                
                if (modelo === null) return; // Si el vendedor cancela, no se añade al carrito
                
                let detalle = modelo.trim() !== "" ? modelo.trim() : "Sin modelo";
                finalName = `${name} (${detalle})`;
                
                // Modificamos el ID temporalmente para que no se sumen fundas de distintos modelos en la misma fila
                uniqueId = `${id}-${detalle.toLowerCase().replace(/\s+/g, '-')}`; 
            }

            let item = cart.find(i => i.id === uniqueId);
            if(item) { 
                item.qty++; 
            } else { 
                cart.push({ id: uniqueId, name: finalName, price, qty: 1 }); 
            }
            updateCartUI();
        }

        function removeFromCart(id) {
            cart = cart.filter(i => i.id !== id);
            updateCartUI();
        }

        function updatePrice(id, newPrice) {
            let item = cart.find(i => i.id === id);
            if(item) {
                item.price = parseFloat(newPrice) || 0;
                updateCartUI();
            }
        }

        function updateCartUI() {
            const container = document.getElementById('cart-items');
            container.innerHTML = '';
            total = 0;

            cart.forEach(item => {
                let subtotal = item.price * item.qty;
                total += subtotal;
                container.innerHTML += `
                    <div class="flex justify-between items-center mb-3 p-3 bg-gray-50 rounded border-l-4 border-cyan-500 shadow-sm">
                        <div class="flex-1">
                            <p class="font-bold text-sm text-gray-800">${item.name}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs text-gray-500 font-bold">${item.qty} x S/</span>
                                <input type="number" step="0.50" value="${item.price.toFixed(2)}" onchange="updatePrice('${item.id}', this.value)" class="w-20 text-xs p-1 border border-gray-300 rounded font-bold text-blue-900 focus:ring-cyan-500 focus:border-cyan-500">
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <p class="font-black text-gray-800">S/ ${subtotal.toFixed(2)}</p>
                            <button onclick="removeFromCart('${item.id}')" class="text-red-500 font-black hover:text-red-700 text-lg">×</button>
                        </div>
                    </div>
                `;
            });
            document.getElementById('cart-total').innerText = 'S/ ' + total.toFixed(2);
        }

        function setPayment(method) {
            document.getElementById('payment-method').value = method;
            if(method === 'efectivo') {
                document.getElementById('btn-efectivo').classList.replace('border-green-200', 'border-green-500');
                document.getElementById('btn-efectivo').classList.add('bg-green-50');
                document.getElementById('btn-yape').classList.replace('border-purple-500', 'border-purple-200');
                document.getElementById('btn-yape').classList.remove('bg-purple-50');
            } else {
                document.getElementById('btn-yape').classList.replace('border-purple-200', 'border-purple-500');
                document.getElementById('btn-yape').classList.add('bg-purple-50');
                document.getElementById('btn-efectivo').classList.replace('border-green-500', 'border-green-200');
                document.getElementById('btn-efectivo').classList.remove('bg-green-50');
            }
        }

        function processSale() {
            if(cart.length === 0) return alert("El ticket está vacío, señor.");
            
            fetch('{{ route('pos.checkout') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    cart: cart,
                    total: total,
                    payment_method: document.getElementById('payment-method').value
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert("Transacción completada, Diego.");
                    cart = [];
                    updateCartUI();
                }
            })
            .catch(error => alert('Fallo de conexión al procesar la venta.'));
        }
    </script>
</body>
</html>