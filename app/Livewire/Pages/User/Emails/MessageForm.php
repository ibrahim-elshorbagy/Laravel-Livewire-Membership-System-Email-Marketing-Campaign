<?php

namespace App\Livewire\Pages\User\Emails;

use App\Models\Admin\Site\SiteSetting;
use Livewire\Component;
use App\Models\Email\EmailMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use App\Rules\ProhibitedWords;
use Mews\Purifier\Facades\Purifier;
use App\Services\HtmlPurifierService;
use HTMLPurifier;
use App\Rules\Base64ImageSize;
use App\Rules\HtmlSize;
use Illuminate\Support\Facades\Log;
use OpenAI;

class MessageForm extends Component
{
    use LivewireAlert;

    public $message_id;
    public $message_title = '';
    public $email_subject = '';
    public $message_html = '';
    public $message_plain_text = '';
    public $sender_name = '';
    public $reply_to_email = '';
    public $sending_status = 'PAUSE';
    public $showPreview = false;
    public $activeEditor = 'advanced'; // 'advanced' for TinyMCE, 'code' for Code Editor
    public $html_size_limit ;
    public $base64_image_size_limit ;

    // AI Generation properties
    public $ai_product_name = '';
    public $ai_product_advantages = '';
    public $ai_target_audience = '';
    public $ai_message_goal = '';
    public $ai_contact_link = '';
    public $ai_company_name = '';
    public $ai_tone = 'professional';
    public $ai_special_offer = '';
    public $ai_language = 'english';
    public $ai_include_icons = false; // New property for icons preference

    public function rules(): array
    {


        if (!(auth()->user()->hasRole('admin') || auth()->user()->can('allow-prohibited-words'))) {
            $messageHtmlRules[] = new ProhibitedWords();
            $messagePlainTextRules[] = new ProhibitedWords();
        }

        return [
            'message_title' => ['required', 'string','max:255'],
            'email_subject' => ['required', 'string','max:255'],
            'sender_name' => ['nullable', 'string','max:255'],
            'reply_to_email' => ['nullable', 'email','max:255'],
            'sending_status' => ['in:RUN,PAUSE'],
            'message_html'  => ['nullable', 'string',new Base64ImageSize(),new HtmlSize()],
            'message_plain_text' => ['nullable', 'string'],
        ];
    }


    public function mount($message = null)
    {
        if ($message) {
            $this->message_id = $message;
            $messageModel = EmailMessage::findOrFail($message);
            $this->fill($messageModel->toArray());
        }
        $this->html_size_limit = SiteSetting::getValue('html_size_limit')?? 1500 ;
        $this->base64_image_size_limit = SiteSetting::getValue('base64_image_size_limit')?? 150 ;
    }

    public function togglePreview()
    {
        $this->showPreview = !$this->showPreview;
    }

    public function getPreviewContent()
    {
        return $this->message_html;
    }





    public function saveMessage()
    {
        $validatedData = $this->validate();

        // $cleanMessage = Purifier::clean($validatedData['message_html'], 'youtube');
        // $validatedData['message_html'] = $cleanMessage;

        $htmlPurifier = new HtmlPurifierService();
        $validatedData['message_html'] = $htmlPurifier->purifyFullHtml($validatedData['message_html']);






        try {
            if ($this->message_id) {
                EmailMessage::where('id', $this->message_id)->update($validatedData);
            } else {
                Auth::user()->emailMessages()->create($validatedData);
            }

            Session::flash('success', 'Emails saved successfully.');
            return $this->redirect(route('user.email-messages'), navigate: true);

        } catch (\Exception $e) {
            $this->alert('error', 'Failed to save message: ' . $e->getMessage(), ['position' => 'bottom-end']);
        }
    }


    public function generateAIMessage()
    {
        // Check if AI is active
        $openai_active = SiteSetting::getValue('openai_active', false);
        if (!$openai_active) {
            $this->alert('error', 'AI integration is not active. Please contact administrator.');
            return;
        }

        // Validate AI form data
        $this->validate([
            'ai_product_name' => 'required|string|max:255',
            'ai_product_advantages' => 'required|string|max:500',
            'ai_target_audience' => 'required|string|max:255',
            'ai_message_goal' => 'required|string|max:255',
            'ai_contact_link' => 'nullable|string|max:255',
            'ai_company_name' => 'required|string|max:255',
            'ai_tone' => 'required|string',
            'ai_special_offer' => 'nullable|string|max:255',
            'ai_language' => 'required|in:english,arabic',
            'ai_include_icons' => 'boolean',
        ]);


        try {
            // Get AI prompt from settings
            $basePrompt = SiteSetting::getValue('prompt', 'Generate a Email Message Plain Text with the following conditions');
            
            // Define variable mappings for admin prompt
            $variables = [
                '$product_name' => $this->ai_product_name,
                '$product_advantages' => $this->ai_product_advantages,
                '$target_audience' => $this->ai_target_audience,
                '$message_goal' => $this->ai_message_goal,
                '$contact_link' => $this->ai_contact_link,
                '$company_name' => $this->ai_company_name,
                '$tone' => $this->ai_tone,
                '$special_offer' => $this->ai_special_offer,
                '$language' => $this->ai_language,
            ];
            
            // Replace variables in the admin prompt
            $prompt = $basePrompt;
            foreach ($variables as $variable => $value) {
                $prompt = str_replace($variable, $value, $prompt);
            }
            
            // Add icon instructions if the option is checked
            if ($this->ai_include_icons) {
                $prompt .= "\n\nPlease include creative and engaging icons/emojis in the content such as ✅ 🧠 🔥 🤖 🚀 💯 ⭐ 🌟 💪 📈 🎯 🎁 💼 📱 💡 and other relevant emojis to emphasize key points and make the email more visually appealing and engaging.";
            }

            $openAi = OpenAI::client(SiteSetting::getValue('openai_api_key', config('services.openai.api_key')));

            $result = $openAi->chat()->create([
                'model' => SiteSetting::getValue('openai_model', config('services.openai.model', 'gpt-4o')),
                'messages' => [
                    [
                        'role' => SiteSetting::getValue('openai_role', config('services.openai.role', 'user')), 
                        'content' => $prompt
                    ],
                ],
            ]);

            // Log::info('AI generation result', [
            //     'prompt' => $prompt,
            //     'result' => $result ?? '',
            //     'response' => $result->choices[0]->message->content ?? '',
            // ]);
            
            $generatedContent = trim($result->choices[0]->message->content ?? '');

            if ($generatedContent) {
                // Update the plain text with generated content
                $this->message_plain_text = $generatedContent;
                
                $this->alert('success', 'AI email template generated successfully!');
                
                // Close the modal
                $this->dispatch('close-modal', 'ai-generation-modal');
                
            } else {
                $this->alert('error', 'Failed to generate content. Please try again.');
            }

        } catch (\Exception $e) {
            $this->alert('error', 'AI generation failed: ' . $e->getMessage());
        } 
    }


    public function render()
    {
        return view('livewire.pages.user.emails.message-form')
            ->layout('layouts.app', ['title' => $this->message_id ? 'Edit Message' : 'New Message']);
    }
}
