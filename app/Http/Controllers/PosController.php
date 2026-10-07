<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    public function index()
    {
        // Si es vendedor y no ha elegido stand en esta sesión, lo enviamos a elegir
        if (Auth::user()->role !== 'admin' && !session()->has('stand_asignado')) {
            $stands = match(Auth::user()->branch) {
                'Plaza Norte' => ['Mi-7', 'A-10', 'A-05', 'J-14', 'L-11', 'L-13'],
                'La Muela' => ['A-1', 'B-1', 'D-18'],
                'Av. 54' => ['AV-54'],
                default => ['Stand Principal']
            };
            return view('pos.select-stand', compact('stands'));
        }

        $products = Product::all();
        return view('pos.index', compact('products'));
    }

    public function setStand(Request $request)
    {
        $request->validate(['stand' => 'required', 'vendedor_nombre' => 'required']);
        session(['stand_asignado' => $request->stand]);
        session(['vendedor_nombre' => $request->vendedor_nombre]);
        return redirect()->route('pos.index');
    }

    public function checkout(Request $request)
    {
        $sedeAsignada = Auth::user()->branch ?? 'Plaza Norte';

        $sale = Sale::create([
            'vendedor_id' => Auth::id(),
            'vendedor_name' => session('vendedor_nombre', Auth::user()->name), // Usa el nombre real
            'sede' => $sedeAsignada,
            'stand' => session('stand_asignado', 'Caja Admin'),
            'metodo_pago' => $request->payment_method,
            'total' => $request->total,
            'productos' => $request->cart,
            'fecha' => now()->timezone('America/Lima')->format('Y-m-d H:i:s')
        ]);

        $columnaSede = match($sedeAsignada) {
            'La Muela' => 'stock_la_muela',
            'Av. 54' => 'stock_av_54',
            default => 'stock_plaza_norte'
        };

        foreach ($request->cart as $item) {
            if(isset($item['id']) && $item['id'] !== 'hidro-ef' && $item['id'] !== 'hidro-ya') {
                $producto = Product::find($item['id']);
                if ($producto) {
                    $producto->$columnaSede = max(0, $producto->$columnaSede - $item['qty']);
                    $producto->save();
                }
            }
        }
        
        return response()->json(['success' => true, 'message' => 'Venta procesada.']);
    }
}