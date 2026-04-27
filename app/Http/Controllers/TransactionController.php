<?php
namespace App\Http\Controllers;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
class TransactionController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function index(Request $request): View
    {
        $query = Auth::user()->transactions()->latest();
        if ($request->type) $query->where('type', $request->type);
        if ($request->status) $query->where('status', $request->status);
        if ($request->date_from) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->date_to) $query->whereDate('created_at', '<=', $request->date_to);
        $transactions = $query->paginate(15)->withQueryString();
        return view('transactions.index', compact('transactions'));
    }
    public function create(): View
    {
        return view('transactions.create');
    }
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type'             => 'required|in:income,expense,transfer',
            'amount'           => 'required|numeric|min:0.01',
            'description'      => 'nullable|string|max:500',
            'reference_number' => 'required|string|unique:transactions|max:50',
        ]);
        $typeMap = [
            'income'  => 'deposit',
            'expense' => 'withdrawal',
        ];
        $validated['type']    = $typeMap[$validated['type']] ?? $validated['type'];
        $validated['user_id'] = auth()->id() ?? Auth::user()->id;
        $validated['status']  = 'pending';
        Transaction::create($validated);
        return redirect()
            ->route('transactions.index')
            ->with('status', 'İşlem başarıyla oluşturuldu.');
    }
    public function show(Transaction $transaction): View
    {
        $this->authorize('view', $transaction);
        return view('transactions.show', compact('transaction'));
    }
    public function edit(Transaction $transaction): View
    {
        $this->authorize('update', $transaction);
        return view('transactions.edit', compact('transaction'));
    }
    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);
        $validated = $request->validate([
            'type'        => 'required|in:deposit,withdrawal,transfer',
            'amount'      => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'status'      => 'required|in:pending,completed,failed',
        ]);
        $transaction->update($validated);
        return redirect()
            ->route('transactions.show', $transaction)
            ->with('status', 'İşlem başarıyla güncellendi.');
    }
    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);
        $transaction->delete();
        return redirect()
            ->route('transactions.index')
            ->with('status', 'İşlem başarıyla silindi.');
    }
}