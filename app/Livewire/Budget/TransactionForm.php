<?php

namespace App\Livewire\Budget;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Livewire\Component;

class TransactionForm extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $type = 'expense';
    public string $account_id = '';
    public string $transfer_account_id = '';
    public string $category_id = '';
    public string $amount = '';
    public string $description = '';
    public string $notes = '';
    public string $date = '';

    protected function rules(): array
    {
        return [
            'type' => 'required|in:income,expense,transfer',
            'account_id' => 'required|exists:accounts,id',
            'transfer_account_id' => $this->type === 'transfer' ? 'required|exists:accounts,id|different:account_id' : 'nullable',
            'category_id' => $this->type !== 'transfer' ? 'nullable|exists:categories,id' : 'nullable',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'date' => 'required|date',
        ];
    }

    public function openModal(?int $id = null): void
    {
        $this->resetValidation();
        $this->editingId = $id;

        if ($id) {
            $t = Transaction::findOrFail($id);
            $this->type = $t->type;
            $this->account_id = $t->account_id;
            $this->category_id = $t->category_id ?? '';
            $this->amount = $t->amount;
            $this->description = $t->description;
            $this->notes = $t->notes ?? '';
            $this->date = $t->date->format('Y-m-d');
        } else {
            $this->reset(['type', 'account_id', 'category_id', 'amount', 'description', 'notes', 'transfer_account_id']);
            $this->type = 'expense';
            $this->date = now()->format('Y-m-d');
        }

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'user_id' => auth()->id(),
            'account_id' => $this->account_id,
            'category_id' => $this->category_id ?: null,
            'transfer_account_id' => $this->type === 'transfer' ? $this->transfer_account_id : null,
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'description' => $this->description,
            'notes' => $this->notes ?: null,
            'date' => $this->date,
        ];

        if ($this->editingId) {
            Transaction::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Transaction modifiée.');
        } else {
            Transaction::create($data);
            session()->flash('success', 'Transaction ajoutée.');
        }

        $this->showModal = false;
        $this->dispatch('transaction-saved');
    }

    public function render()
    {
        return view('livewire.budget.transaction-form', [
            'accounts' => auth()->user()->accounts()->where('is_active', true)->get(),
            'incomeCategories' => auth()->user()->categories()->where('type', 'income')->where('is_active', true)->get(),
            'expenseCategories' => auth()->user()->categories()->where('type', 'expense')->where('is_active', true)->get(),
        ]);
    }
}
