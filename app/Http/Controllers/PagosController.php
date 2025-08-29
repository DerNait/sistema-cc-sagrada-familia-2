<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PagosController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:payments.read')->only(['index', 'show']);
        $this->middleware('permission:payments.create')->only(['create', 'store']);
        $this->middleware('permission:payments.edit')->only(['edit']);
        $this->middleware('permission:payments.update')->only(['update']);
        $this->middleware('permission:payments.delete')->only(['destroy']);
        $this->middleware('permission:payments.pay')->only(['pay']);

    }

    public function index()
    {
        $pagos = Pago::all();
        return view('pagos.index', compact('pagos'));
    }

    public function show(Pago $pago)
    {
        return view('pagos.show', compact('pago'));
    }

    public function create()
    {
        return view('pagos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'monto' => 'required|numeric',
            'descripcion' => 'nullable|string',
        ]);

        $pago = Pago::create($data);

        return redirect()->route('payments.index')
                         ->with('success', 'Pago creado correctamente.');
    }

    public function edit(Pago $pago)
    {
        return view('pagos.edit', compact('pago'));
    }

    public function update(Request $request, Pago $pago)
    {
        $data = $request->validate([
            'monto' => 'required|numeric',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:pendiente,pagado,aprobado,rechazado',
        ]);

        $pago->update($data);

        return redirect()->route('payments.index')
                         ->with('success', 'Pago actualizado correctamente.');
    }

    public function destroy(Pago $pago)
    {
        $pago->delete();

        return redirect()->route('payments.index')
                         ->with('success', 'Pago eliminado correctamente.');
    }

    public function pay(Pago $pago)
    {
        $pago->update(['estado' => 'pagado']);

        return redirect()->route('payments.index')
                         ->with('success', 'Pago registrado como pagado.');
    }
}
