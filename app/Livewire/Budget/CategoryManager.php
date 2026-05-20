<?php

namespace App\Livewire\Budget;

use App\Models\Category;
use Livewire\Component;

class CategoryManager extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $activeTab = 'expense';

    public string $name = '';
    public string $type = 'expense';
    public string $icon = '📦';
    public string $color = '#6b7280';
    public string $monthly_budget = '';

    protected $rules = [
        'name' => 'required|string|max:100',
        'type' => 'required|in:income,expense',
        'icon' => 'required|string|max:10',
        'color' => 'required|string',
        'monthly_budget' => 'nullable|numeric|min:0',
    ];

    public function openModal(?int $id = null, string $type = 'expense'): void
    {
        $this->resetValidation();
        $this->editingId = $id;

        if ($id) {
            $cat = Category::findOrFail($id);
            $this->name = $cat->name;
            $this->type = $cat->type;
            $this->icon = $cat->icon;
            $this->color = $cat->color;
            $this->monthly_budget = $cat->monthly_budget ?? '';
        } else {
            $this->reset(['name', 'icon', 'monthly_budget']);
            $this->type = $type;
            $this->color = '#6b7280';
            $this->icon = '📦';
        }
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();
        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'icon' => $this->icon,
            'color' => $this->color,
            'monthly_budget' => $this->monthly_budget ?: null,
        ];

        if ($this->editingId) {
            Category::findOrFail($this->editingId)->update($data);
        } else {
            $data['user_id'] = auth()->id();
            Category::create($data);
        }

        $this->showModal = false;
        session()->flash('success', 'Catégorie sauvegardée.');
    }

    public function delete(int $id): void
    {
        $cat = Category::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        if ($cat->transactions()->count()) {
            session()->flash('error', 'Catégorie utilisée par des transactions.');
            return;
        }
        $cat->delete();
        session()->flash('success', 'Catégorie supprimée.');
    }

    public function render()
    {
        $user = auth()->user();
        return view('livewire.budget.category-manager', [
            'expenseCategories' => $user->categories()->where('type', 'expense')->orderBy('name')->get(),
            'incomeCategories' => $user->categories()->where('type', 'income')->orderBy('name')->get(),
        ]);
    }
}
