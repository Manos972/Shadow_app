<?php

namespace App\Livewire\Budget;

use App\Models\Transaction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class TransactionList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $type = '';

    #[Url]
    public string $account = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    #[Url]
    public string $sortBy = 'date';

    #[Url]
    public string $sortDir = 'desc';

    public int $perPage = 20;

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedType(): void { $this->resetPage(); }
    public function updatedAccount(): void { $this->resetPage(); }
    public function updatedCategory(): void { $this->resetPage(); }

    public function sort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDir = 'desc';
        }
    }

    public function delete(int $id): void
    {
        $transaction = Transaction::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $transaction->delete();
        session()->flash('success', 'Transaction supprimée.');
    }

    public function render()
    {
        $query = auth()->user()->transactions()
            ->with(['account', 'category'])
            ->when($this->search, fn($q) => $q->where('description', 'like', "%{$this->search}%"))
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->when($this->account, fn($q) => $q->where('account_id', $this->account))
            ->when($this->category, fn($q) => $q->where('category_id', $this->category))
            ->when($this->dateFrom, fn($q) => $q->where('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->where('date', '<=', $this->dateTo))
            ->orderBy($this->sortBy, $this->sortDir);

        $transactions = $query->paginate($this->perPage);
        $accounts = auth()->user()->accounts()->where('is_active', true)->get();
        $categories = auth()->user()->categories()->where('is_active', true)->get();

        $summary = [
            'income' => $query->clone()->where('type', 'income')->sum('amount'),
            'expense' => $query->clone()->where('type', 'expense')->sum('amount'),
        ];

        return view('livewire.budget.transaction-list', compact('transactions', 'accounts', 'categories', 'summary'));
    }
}
