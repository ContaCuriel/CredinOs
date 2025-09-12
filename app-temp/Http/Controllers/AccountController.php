<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtenemos solo las cuentas de nivel superior (sin padre)
        // y cargamos sus hijas de forma recursiva (eager loading)
        // Tambien ordenamos por código y paginamos.
        $accounts = Account::with('children')
            ->whereNull('parent_id')
            ->orderBy('code')
            ->paginate(50); // Puedes ajustar este número

        return view('accounts.index', compact('accounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $accounts = Account::orderBy('code')->get(); // Para el selector de cuenta padre
        return view('accounts.create', compact('accounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:accounts,code',
            'type' => 'required|in:activo,pasivo,capital,ingresos,costos,gastos',
            'parent_id' => 'nullable|exists:accounts,id',
            'description' => 'nullable|string',
        ]);

        Account::create($request->all());

        return redirect()->route('accounts.index')->with('success', 'Cuenta creada exitosamente.');
    }

    /**
     * Display the specified resource.
     * (Normalmente no se usa en un CRUD de este tipo, pero lo dejamos por si acaso)
     */
    public function show(Account $account)
    {
        return view('accounts.show', compact('account'));
    }

    /**
     * Show the form for editing the specified resource.
     * ESTA ES LA FUNCIÓN QUE ESTABA DUPLICADA. AHORA SOLO HAY UNA.
     */
    public function edit(Account $account)
    {
        // Obtenemos todas las cuentas para el selector de "Cuenta Padre"
        $allAccounts = Account::orderBy('code')->get();
        
        // Excluimos la cuenta actual y todas sus descendientes de la lista de posibles padres
        // para evitar que una cuenta sea padre de sí misma o de sus propios padres.
        $descendantIds = $this->getDescendantIds($account);
        $descendantIds[] = $account->id; // La propia cuenta tampoco puede ser su padre
        
        $accounts = $allAccounts->whereNotIn('id', $descendantIds);
        
        return view('accounts.edit', compact('account', 'accounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Account $account)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            // La regla unique ignora el ID de la cuenta actual al validar
            'code' => 'required|string|unique:accounts,code,' . $account->id,
            'type' => 'required|in:activo,pasivo,capital,ingresos,costos,gastos',
            'parent_id' => 'nullable|exists:accounts,id',
            'description' => 'nullable|string',
        ]);

        $account->update($request->all());

        return redirect()->route('accounts.index')->with('success', 'Cuenta actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account)
    {
        // Gracias al onDelete('cascade') en la migración, al borrar una cuenta padre
        // se borrarán todas sus cuentas hijas automáticamente.
        $account->delete(); 
        return redirect()->route('accounts.index')->with('success', 'Cuenta y subcuentas eliminadas exitosamente.');
    }

    /**
     * Helper para obtener los IDs de todas las cuentas descendientes (hijas, nietas, etc.)
     * de forma recursiva.
     */
    private function getDescendantIds($account)
    {
        $ids = [];
        foreach ($account->children as $child) {
            $ids[] = $child->id;
            // Llamada recursiva para obtener los IDs de los hijos de este hijo
            $ids = array_merge($ids, $this->getDescendantIds($child));
        }
        return $ids;
    }
}
