<?php

namespace App\Livewire\Pages\Admin\SiteSettings;

use App\Models\Admin\Site\ProhibitedWord;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class ProhibitedWords extends Component
{
    use WithPagination, LivewireAlert;

    public $search = '';
    public $sortField = 'word';
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $selectedWords = [];
    public $selectPage = false;
    public $newWords = '';
    public $editingWord = null;
    public $editedWord = '';

    public function updatedSelectPage($value)
    {
        if ($value) {
            $this->selectedWords = $this->words->pluck('id')->map(fn($id) => (string) $id);
        } else {
            $this->selectedWords = [];
        }
    }

    public function addWords()
    {
        $this->validate([
            'newWords' => 'required|string'
        ]);

        $words = collect(preg_replace('/\\,/', '__COMMA__', $this->newWords))
            ->flatMap(fn($text) => explode(',', $text))
            ->map(fn($word) => str_replace('__COMMA__', ',', trim($word)))
            ->filter();

        foreach ($words as $word) {
            ProhibitedWord::create(['word' => $word]);
        }

        cache()->forget('prohibited_words');
        $this->newWords = '';
        $this->alert('success', 'Words added successfully', ['position' => 'bottom-end']);
    }

    public function startEditing($id)
    {
        $word = ProhibitedWord::find($id);
        $this->editingWord = $word->id;
        $this->editedWord = $word->word;
    }

    public function updateWord()
    {
        $this->validate([
            'editedWord' => 'required|string'
        ]);

        $word = ProhibitedWord::find($this->editingWord);
        $word->update(['word' => $this->editedWord]);

        cache()->forget('prohibited_words');
        $this->editingWord = null;
        $this->editedWord = '';
        $this->alert('success', 'Word updated successfully', ['position' => 'bottom-end']);
    }

    public function deleteWord($id)
    {
        ProhibitedWord::destroy($id);
        cache()->forget('prohibited_words');
        $this->alert('success', 'Word deleted successfully', ['position' => 'bottom-end']);
    }

    public function bulkDelete()
    {
        ProhibitedWord::whereIn('id', $this->selectedWords)->delete();
        $this->selectedWords = [];
        $this->selectPage = false;
        cache()->forget('prohibited_words');
        $this->alert('success', 'Selected words deleted successfully', ['position' => 'bottom-end']);
    }

    public function getWordsProperty()
    {
        return ProhibitedWord::where('word', 'like', '%' . $this->search . '%')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.pages.admin.site-settings.prohibited-words', [
            'words' => $this->words
        ])->layout('layouts.app', ['title' => 'Prohibited Words']);
    }
}
