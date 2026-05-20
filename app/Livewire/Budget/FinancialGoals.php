<?php
namespace App\Livewire\Budget;

use App\Models\FinancialGoal;
use Livewire\Component;

class FinancialGoals extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $type = 'savings';
    public string $target_amount = '';
    public string $current_amount = '';
    public string $monthly_contribution = '';
    public string $target_date = '';
    public string $icon = '🎯';
    public string $color = '#3b82f6';

    protected $rules = [
        'name' => 'required|string|max:100',
        'type' => 'required',
        'target_amount' => 'required|numeric|min:1',
        'current_amount' => 'required|numeric|min:0',
        'monthly_contribution' => 'nullable|numeric|min:0',
        'target_date' => 'nullable|date',
        'icon' => 'required|string|max:10',
        'color' => 'required|string',
    ];

    public function openModal(?int $id = null): void
    {
        $this->resetValidation();
        $this->editingId = $id;
        if ($id) {
            $goal = FinancialGoal::findOrFail($id);
            $this->fill($goal->only(['name', 'type', 'target_amount', 'current_amount', 'monthly_contribution', 'icon', 'color']));
            $this->target_date = $goal->target_date?->format('Y-m-d') ?? '';
        } else {
            $this->reset(['name', 'type', 'target_amount', 'current_amount', 'monthly_contribution', 'target_date']);
            $this->type = 'savings';
            $this->icon = '🎯';
            $this->color = '#3b82f6';
            $this->current_amount = '0';
        }
        $this->showModal = true;
    }

    public function updatedType(): void
    {
        $presets = [
            'tfsa_max' => ['icon' => '🏦', 'color' => '#10b981', 'name' => 'Maximiser le CÉLI', 'target_amount' => '7000'],
            'rrsp_max' => ['icon' => '📊', 'color' => '#8b5cf6', 'name' => 'Maximiser le REER', 'target_amount' => '31560'],
            'emergency_fund' => ['icon' => '🛡️', 'color' => '#f59e0b', 'name' => 'Fonds d\'urgence (6 mois)', 'target_amount' => '15000'],
            'retirement' => ['icon' => '🏖️', 'color' => '#06b6d4', 'name' => 'Retraite', 'target_amount' => '1000000'],
        ];
        if (isset($presets[$this->type]) && !$this->editingId) {
            foreach ($presets[$this->type] as $k => $v) $this->$k = $v;
        }
    }

    public function save(): void
    {
        $this->validate();
        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'target_amount' => (float) $this->target_amount,
            'current_amount' => (float) $this->current_amount,
            'monthly_contribution' => (float) ($this->monthly_contribution ?? 0),
            'target_date' => $this->target_date ?: null,
            'icon' => $this->icon,
            'color' => $this->color,
        ];

        if ($this->editingId) {
            FinancialGoal::findOrFail($this->editingId)->update($data);
        } else {
            $data['user_id'] = auth()->id();
            FinancialGoal::create($data);
        }

        $this->showModal = false;
        session()->flash('success', 'Objectif sauvegardé.');
    }

    public function updateProgress(int $id, float $amount): void
    {
        FinancialGoal::where('id', $id)->where('user_id', auth()->id())->update(['current_amount' => $amount]);
    }

    public function delete(int $id): void
    {
        FinancialGoal::where('id', $id)->where('user_id', auth()->id())->delete();
    }

    public function render()
    {
        $goals = auth()->user()->financialGoals ?? FinancialGoal::where('user_id', auth()->id())->where('is_active', true)->orderBy('created_at')->get();
        return view('livewire.budget.financial-goals', compact('goals'));
    }
}
