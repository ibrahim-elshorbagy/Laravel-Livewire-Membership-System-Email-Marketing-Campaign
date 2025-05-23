<?php

namespace App\Livewire\Pages\Admin\SiteSettings\System\SystemEmails;

use Livewire\Component;
use App\Models\Admin\Site\SystemSetting\SystemEmail;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\Session;
use App\Rules\ProhibitedWords;
use App\Models\Admin\Site\SystemSetting\SystemEmailList;

class SystemEmailsForm extends Component
{
    use LivewireAlert;

    public $email_id;
    public $name = '';
    public $slug = '';
    public $email_subject = '';
    public $message_html = '';
    public $showPreview = false;
    public $list_id = null;
    public $activeEditor = 'advanced'; // 'advanced' for TinyMCE, 'code' for Code Editor

    public function rules(): array
    {

        return [
            'name' => ['required', 'string','max:255'],
            'slug' => ['required', 'string','max:255','unique:system_emails,slug,'.$this->email_id],
            'email_subject' => ['nullable', 'string','max:255'],
            'message_html'  => ['nullable', 'string'],
            'list_id' => ['nullable', 'exists:system_email_lists,id'],
        ];
    }

    public function mount($email = null)
    {
        if ($email) {
            $this->email_id = $email;
            $emailModel = SystemEmail::findOrFail($email);
            $this->fill($emailModel->toArray());
            $this->list_id = $emailModel->list_id;
        }
    }

    public function getListsProperty()
    {
        return SystemEmailList::all();
    }

    public function togglePreview()
    {
        $this->showPreview = !$this->showPreview;
    }

    public function getPreviewContent()
    {
        return $this->message_html;
    }

    public function saveEmail()
    {
        $validatedData = $this->validate();

        try {
            if ($this->email_id) {
                SystemEmail::where('id', $this->email_id)->update($validatedData);
            } else {
                SystemEmail::create($validatedData);
            }

            Session::flash('success', 'System email template saved successfully.');
            return $this->redirect(route('admin.site-system-emails'), navigate: true);

        } catch (\Exception $e) {
            $this->alert('error', 'Failed to save email template: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.site-settings.system.system-emails.system-emails-form')
            ->layout('layouts.app', ['title' => $this->email_id ? 'Edit System Email' : 'New System Email']);
    }
}
