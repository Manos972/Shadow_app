<?php

namespace App\Livewire\Portfolio;

use App\Models\StockAlert;
use Livewire\Component;

class AlertManager extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $symbol = '';
    public string $name = '';
    public string $type = 'price_below';
    public string $threshold = '';
    public bool $notify_email = true;

    protected $rules = [
        'symbol' => 'required|string|max:20',
        'type' => 'required|in:price_above,price_below,change_percent_above,change_percent_below,rsi_above,rsi_below',
        'threshold' => 'required|numeric|min:0',
        'notify_email' => 'boolean',
    ];

    public function openModal(?int $id = null, ?string $symbol = null): void
    {
        $this->resetValidation();
        $this->editingId = $id;
        if ($id) {
            $alert = StockAlert::findOrFail($id);
            $this->symbol = $alert->symbol;
            $this->name = $alert->name ?? '';
            $this->type = $alert->type;
            $this->threshold = $alert->threshold;
            $this->notify_email = $alert->notify_email;
        } else {
            $this->reset(['symbol', 'name', 'type', 'threshold']);
            $this->symbol = $symbol ?? '';
            $this->type = 'price_below';
            $this->notify_email = true;
        }
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();
        $data = [
            'symbol' => strtoupper(trim($this->symbol)),
            'name' => $this->name ?: null,
            'type' => $this->type,
            'threshold' => (float) $this->threshold,
            'notify_email' => $this->notify_email,
            'is_active' => true,
            'triggered_at' => null,
        ];

        if ($this->editingId) {
            StockAlert::findOrFail($this->editingId)->update($data);
        } else {
            $data['user_id'] = auth()->id();
            StockAlert::create($data);
        }

        $this->showModal = false;
        session()->flash('success', 'Alerte sauvegardée.');
    }

    public function delete(int $id): void
    {
        StockAlert::where('id', $id)->where('user_id', auth()->id())->delete();
        session()->flash('success', 'Alerte supprimée.');
    }

    public function reactivate(int $id): void
    {
        StockAlert::where('id', $id)->where('user_id', auth()->id())->update(['is_active' => true, 'triggered_at' => null]);
        session()->flash('success', 'Alerte réactivée.');
    }

    public function render()
    {
        $alerts = auth()->user()->stockAlerts()->orderByDesc('created_at')->get();
        return view('livewire.portfolio.alert-manager', compact('alerts'));
    }
}
