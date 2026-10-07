<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        // Seguridad: Vendedor sin caja asignada no pasa
        if (Auth::user()->role !== 'admin' && !session()->has('stand_asignado')) {
            return redirect()->route('pos.index');
        }

        $dateParam = $request->query('date');
        $sedeParam = $request->query('sede');
        $standParam = $request->query('stand');

        // FASE 2: Si hay parámetros, mostramos el reporte detallado
        if ($dateParam && $sedeParam && $standParam) {
            
            // Bloqueo de seguridad cruzada para vendedores
            if (Auth::user()->role !== 'admin') {
                if ($sedeParam !== Auth::user()->branch || $standParam !== session('stand_asignado')) {
                    abort(403, 'Acceso denegado a esta bóveda.');
                }
            }

            $ventasHistorial = Sale::where('fecha', 'like', $dateParam . '%')
                                   ->where('sede', $sedeParam)
                                   ->where('stand', $standParam)
                                   ->orderBy('fecha', 'desc')
                                   ->get();

            $totalBruto = $ventasHistorial->sum('total');
            $totalYape = $ventasHistorial->where('metodo_pago', 'yape')->sum('total');
            $totalEfectivo = $ventasHistorial->where('metodo_pago', 'efectivo')->sum('total');

            $totalHidrogel = 0;
            foreach ($ventasHistorial as $venta) {
                $productos = is_string($venta->productos) ? json_decode($venta->productos, true) : ($venta->productos ?? []);
                foreach ($productos as $item) {
                    if (stripos($item['name'] ?? '', 'Hidrogel') !== false || in_array($item['id'] ?? '', ['hidro-ef', 'hidro-ya'])) {
                        $totalHidrogel += ($item['price'] * $item['qty']);
                    }
                }
            }

            $gastos = 0;
            if (Auth::user()->role !== 'admin') {
                if ($totalBruto > 0) $gastos += 9.00;
                if ($totalBruto >= 800) $gastos += 20.00;
                elseif ($totalBruto >= 400) $gastos += 10.00;
            }

            $restanteCaja = $totalEfectivo - $gastos;

            return view('history', compact(
                'dateParam', 'sedeParam', 'standParam', 'ventasHistorial',
                'totalBruto', 'totalYape', 'totalEfectivo', 'totalHidrogel', 'gastos', 'restanteCaja'
            ));
        }

        // FASE 1: Lista general de reportes pasados
        $query = Sale::query();
        if (Auth::user()->role !== 'admin') {
            $query->where('sede', Auth::user()->branch)
                  ->where('stand', session('stand_asignado'));
        }

        $allSales = $query->orderBy('fecha', 'desc')->get();

        // Agrupamos las ventas por día, sede, stand y vendedor
        $sessions = $allSales->groupBy(function($sale) {
            $date = substr($sale->fecha, 0, 10);
            return $date . '|' . $sale->sede . '|' . $sale->stand . '|' . $sale->vendedor_name;
        })->map(function($group, $key) {
            $parts = explode('|', $key);
            return [
                'date' => $parts[0],
                'sede' => $parts[1],
                'stand' => $parts[2],
                'vendedor' => $parts[3] ?? 'Desconocido',
                'total_sales' => $group->count(),
                'total_revenue' => $group->sum('total')
            ];
        })->values();

        return view('history', compact('sessions', 'dateParam'));
    }
}