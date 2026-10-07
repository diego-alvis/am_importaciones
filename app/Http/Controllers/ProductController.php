<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return view('inventory.index', compact('products'));
    }

    public function create()
    {
        return view('inventory.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'sku' => 'required|unique:products',
            'name' => 'required',
            'category' => 'required',
            'sale_price' => 'required|numeric',
        ]);

        Product::create($request->all());
        
        return redirect()->route('inventory.index');
    }
}