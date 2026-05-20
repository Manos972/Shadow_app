<?php

namespace App\Livewire\Budget;

use App\Models\Account;
use Livewire\Component;
use Livewire\Attributes\Rule;

class AccountManager extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;

    #[Rule('required|string|max:100')]
    public string $name = '';

    #[Rule('required|in:checking,savings,cash,credit,investment,crypto')]
    public string $type = 'checking';

    #[Rule('required|numeric|min:0')]
    public string $balance = '0';

    #[Rule('required|string|size:3')]
    public string $currency = 'EUR';

    #[Rule('nullable|string')]
    public string $color = '#3b82f6';

    #[Rule('nullable|string')]
    public string $notes = '';

    public function openModal(?int $id = null): void
    {
        $this->resetValidation();
        $this->editingId = $id;

        if ($id) {
            $account = Account::findOrFail($id);
            $this->name = $account->name;
            $this->type = $account->type;
            $this->balance = $account->balance;
            $this->currency = $account->currency;
            $this->color = $account->color;
            $this->notes = $account->notes ?? '';
        } else {
            $this->reset(['name', 'type', 'balance', 'currency', 'color', 'notes']);
            $this->currency = auth()->user()->currency ?? 'EUR';
            $this->color = '#3b82f6';
        }
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'currency' => $this->currency,
            'color' => $this->color,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingId) {
            $account = Account::findOrFail($this->editingId);
            $account->update($data);
            session()->flash('success', 'Compte mis à jour.');
        } else {
            $data['user_id'] = auth()->id();
            $data['balance'] = (float) $this->balance;
            $data['initial_balance'] = (float) $this->balance;
            Account::create($data);
            session()->flash('success', 'Compte créé.');
        }

        $this->showModal = false;
        $this->dispatch('account-updated');
    }

    public function delete(int $id): void
    {
        $account = Account::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        if ($account->transactions()->count() > 0) {
            session()->flash('error', 'Impossible de supprimer un compte avec des transactions.');
            return;
        }
        $account->delete();
        session()->flash('success', 'Compte supprimé.');
    }

    public function render()
    {
        $accounts = auth()->user()->accounts()->withCount('transactions')->orderBy('type')->get();
        $totals = [
            'checking' => $accounts->where('type', 'checking')->sum('balance'),
            'savings' => $accounts->where('type', 'savings')->sum('balance'),
            'cash' => $accounts->where('type', 'cash')->sum('balance'),
            'credit' => $accounts->where('type', 'credit')->sum('balance'),
        ];
        return view('livewire.budget.account-manager', compact('accounts', 'totals'));
    }
}
