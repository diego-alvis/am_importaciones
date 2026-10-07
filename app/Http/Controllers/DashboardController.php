<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->role !== 'admin' && !session()->has('stand_asignado')) {
            return redirect()->route('pos.index');
        }

        $hoy = Carbon::today('America/Lima')->format('Y-m-d');
        $query = Sale::where('fecha', 'like', $hoy . '%');

        // Filtros jerárquicos
        $sedeFiltro = $request->query('sede');
        $standFiltro = $request->query('stand');

        if (Auth::user()->role !== 'admin') {
            $sedeFiltro = Auth::user()->branch;
            $standFiltro = session('stand_asignado');
        }

        if ($sedeFiltro) $query->where('sede', $sedeFiltro);
        if ($standFiltro) $query->where('stand', $standFiltro);

        // Obtenemos ventas filtradas
        $ventasHoy = $query->orderBy('fecha', 'desc')->get();

        $totalBruto = $ventasHoy->sum('total');
        $totalYape = $ventasHoy->where('metodo_pago', 'yape')->sum('total');
        $totalEfectivo = $ventasHoy->where('metodo_pago', 'efectivo')->sum('total');

        // Extracción profunda de ventas de Hidrogel
        $totalHidrogel = 0;
        foreach ($ventasHoy as $venta) {
            $productos = is_string($venta->productos) ? json_decode($venta->productos, true) : ($venta->productos ?? []);
            foreach ($productos as $item) {
                if (stripos($item['name'] ?? '', 'Hidrogel') !== false || in_array($item['id'] ?? '', ['hidro-ef', 'hidro-ya'])) {
                    $totalHidrogel += ($item['price'] * $item['qty']);
                }
            }
        }

        // Lógica estricta de viáticos (Exclusiva para Vendedores)
        $gastos = 0;
        if (Auth::user()->role !== 'admin') {
            if ($totalBruto > 0) $gastos += 9.00;
            if ($totalBruto >= 800) $gastos += 20.00;
            elseif ($totalBruto >= 400) $gastos += 10.00;
        }

        $restanteCaja = $totalEfectivo - $gastos;

        // Listas dinámicas para los selectores del Admin
        $todasLasVentas = Sale::where('fecha', 'like', $hoy . '%')->get();
        $sedesDisponibles = $todasLasVentas->pluck('sede')->unique()->filter();
        $standsDisponibles = $sedeFiltro ? $todasLasVentas->where('sede', $sedeFiltro)->pluck('stand')->unique()->filter() : [];

        return view('dashboard', compact(
            'totalBruto', 'totalYape', 'totalEfectivo', 'totalHidrogel', 'gastos', 'restanteCaja', 'ventasHoy', 
            'sedeFiltro', 'standFiltro', 'sedesDisponibles', 'standsDisponibles'
        ));
    }
}