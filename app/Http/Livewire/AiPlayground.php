<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use App\Ai\Agents\PlaygroundAgent;
use Laravel\Ai\Image;
use Laravel\Ai\Audio;
use Laravel\Ai\Transcription;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Reranking;
use Throwable;

class AiPlayground extends Component
{
    use WithFileUploads;

    // ── Mode ──────────────────────────────────────────────────────────────────
    public string $mode = 'agent'; // agent|stream|image|audio|transcription|embeddings|reranking

    // ── Chat history ──────────────────────────────────────────────────────────
    // Each entry: ['role'=>'user'|'ai', 'type'=>'text'|'image'|'audio'|'code'|'reranking'|'file'|'error'|'stream', 'content'=>..., 'meta'=>[...]]
    public array $messages = [];

    // ── Shared input ──────────────────────────────────────────────────────────
    public string $inputText = '';

    // ── File upload (transcription) ───────────────────────────────────────────
    public $audioFile = null;

    // ── Reranking extras ──────────────────────────────────────────────────────
    public string $rerankingQuery     = '';
    public string $rerankingDocuments = "Laravel is a PHP web application framework.\nDjango is a Python web framework.\nReact is a JavaScript library for building user interfaces.";

    // ── Settings panel ────────────────────────────────────────────────────────
    public bool   $showSettings = false;

    // Per-feature provider selection (persisted in session)
    public string $providerAgent         = 'openai';
    public string $providerStream        = 'openai';
    public string $providerImage         = 'gemini';
    public string $providerAudio         = 'openai';
    public string $providerTranscription = 'openai';
    public string $providerEmbeddings    = 'openai';
    public string $providerReranking     = 'cohere';

    // Per-feature model name (free text, provider-specific)
    public string $modelAgent         = 'gpt-4o';
    public string $modelStream        = 'gpt-4o';
    public string $modelImage         = 'gemini-2.0-flash-preview-image-generation';
    public string $modelAudio         = 'tts-1';
    public string $modelTranscription = 'whisper-1';
    public string $modelEmbeddings    = 'text-embedding-3-small';
    public string $modelReranking     = 'rerank-english-v3.0';

    // API key overrides (stored in session only, never persisted to DB)
    public string $keyOpenai      = '';
    public string $keyAnthropic   = '';
    public string $keyGemini      = '';
    public string $keyGroq        = '';
    public string $keyMistral     = '';
    public string $keyDeepseek    = '';
    public string $keyCohere      = '';
    public string $keyElevenLabs  = '';
    public string $keyOpenrouter  = '';
    public string $keyXai         = '';
    public string $keyJina        = '';
    public string $keyVoyageai    = '';
    public string $ollamaUrl      = 'http://localhost:11434';

    // Settings save feedback
    public ?string $settingsSaved = null;

    // ── Provider catalogue ────────────────────────────────────────────────────
    public static array $providers = [
        'openai'     => ['label' => 'OpenAI',        'color' => '#10a37f'],
        'anthropic'  => ['label' => 'Anthropic',     'color' => '#d97706'],
        'gemini'     => ['label' => 'Gemini',         'color' => '#4285f4'],
        'groq'       => ['label' => 'Groq',           'color' => '#f55036'],
        'mistral'    => ['label' => 'Mistral',        'color' => '#ff7000'],
        'deepseek'   => ['label' => 'DeepSeek',       'color' => '#1a73e8'],
        'cohere'     => ['label' => 'Cohere',         'color' => '#39c5bb'],
        'eleven'     => ['label' => 'ElevenLabs',     'color' => '#a855f7'],
        'openrouter' => ['label' => 'OpenRouter',     'color' => '#6366f1'],
        'xai'        => ['label' => 'xAI',            'color' => '#000000'],
        'jina'       => ['label' => 'Jina',           'color' => '#0891b2'],
        'voyageai'   => ['label' => 'VoyageAI',       'color' => '#7c3aed'],
        'ollama'     => ['label' => 'Ollama (local)', 'color' => '#16a34a'],
        'azure'      => ['label' => 'Azure OpenAI',   'color' => '#0078d4'],
    ];

    // Suggested models per provider
    public static array $modelSuggestions = [
        'openai'     => ['gpt-4o', 'gpt-4o-mini', 'gpt-4-turbo', 'gpt-3.5-turbo', 'tts-1', 'tts-1-hd', 'whisper-1', 'text-embedding-3-small', 'text-embedding-3-large', 'dall-e-3'],
        'anthropic'  => ['claude-opus-4-5', 'claude-sonnet-4-5', 'claude-3-5-haiku-latest', 'claude-3-opus-latest'],
        'gemini'     => ['gemini-2.0-flash', 'gemini-2.0-flash-lite', 'gemini-1.5-pro', 'gemini-1.5-flash', 'gemini-2.0-flash-preview-image-generation'],
        'groq'       => ['llama-3.3-70b-versatile', 'llama-3.1-8b-instant', 'mixtral-8x7b-32768', 'gemma2-9b-it'],
        'mistral'    => ['mistral-large-latest', 'mistral-small-latest', 'open-mistral-7b', 'codestral-latest'],
        'deepseek'   => ['deepseek-chat', 'deepseek-reasoner'],
        'cohere'     => ['command-r-plus', 'command-r', 'rerank-english-v3.0', 'rerank-multilingual-v3.0', 'embed-english-v3.0'],
        'eleven'     => ['eleven_multilingual_v2', 'eleven_turbo_v2_5', 'eleven_flash_v2_5'],
        'openrouter' => ['openai/gpt-4o', 'anthropic/claude-3.5-sonnet', 'google/gemini-pro-1.5', 'meta-llama/llama-3.1-70b-instruct'],
        'xai'        => ['grok-2-latest', 'grok-2-vision-latest', 'grok-beta'],
        'jina'       => ['jina-embeddings-v3', 'jina-reranker-v2-base-multilingual'],
        'voyageai'   => ['voyage-3', 'voyage-3-lite', 'rerank-2', 'rerank-2-lite'],
        'ollama'     => ['llama3.2', 'llama3.1', 'mistral', 'codellama', 'phi3', 'gemma2'],
        'azure'      => ['gpt-4o', 'gpt-4', 'gpt-35-turbo'],
    ];

    // ── Lifecycle ─────────────────────────────────────────────────────────────
    public function mount(): void
    {
        $saved = session('ai_playground_settings', []);
        foreach ($saved as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    // ── Mode switching ────────────────────────────────────────────────────────
    public function setMode(string $mode): void
    {
        $this->mode      = $mode;
        $this->inputText = '';
        $this->audioFile = null;
    }

    // ── Clear chat ────────────────────────────────────────────────────────────
    public function clearChat(): void
    {
        $this->messages  = [];
        $this->inputText = '';
        $this->audioFile = null;
    }

    // ── Settings ──────────────────────────────────────────────────────────────
    public function toggleSettings(): void
    {
        $this->showSettings  = !$this->showSettings;
        $this->settingsSaved = null;
    }

    public function saveSettings(): void
    {
        $fields = [
            'providerAgent','providerStream','providerImage','providerAudio',
            'providerTranscription','providerEmbeddings','providerReranking',
            'modelAgent','modelStream','modelImage','modelAudio',
            'modelTranscription','modelEmbeddings','modelReranking',
            'keyOpenai','keyAnthropic','keyGemini','keyGroq','keyMistral',
            'keyDeepseek','keyCohere','keyElevenLabs','keyOpenrouter',
            'keyXai','keyJina','keyVoyageai','ollamaUrl',
        ];

        $data = [];
        foreach ($fields as $f) {
            $data[$f] = $this->{$f};
        }
        session(['ai_playground_settings' => $data]);
        $this->applyRuntimeConfig();
        $this->settingsSaved = 'Settings saved successfully.';
    }

    private function applyRuntimeConfig(): void
    {
        $map = [
            'keyOpenai'     => 'ai.providers.openai.key',
            'keyAnthropic'  => 'ai.providers.anthropic.key',
            'keyGemini'     => 'ai.providers.gemini.key',
            'keyGroq'       => 'ai.providers.groq.key',
            'keyMistral'    => 'ai.providers.mistral.key',
            'keyDeepseek'   => 'ai.providers.deepseek.key',
            'keyCohere'     => 'ai.providers.cohere.key',
            'keyElevenLabs' => 'ai.providers.eleven.key',
            'keyOpenrouter' => 'ai.providers.openrouter.key',
            'keyXai'        => 'ai.providers.xai.key',
            'keyJina'       => 'ai.providers.jina.key',
            'keyVoyageai'   => 'ai.providers.voyageai.key',
            'ollamaUrl'     => 'ai.providers.ollama.url',
        ];

        foreach ($map as $prop => $configKey) {
            if (!empty($this->{$prop})) {
                config([$configKey => $this->{$prop}]);
            }
        }

        config([
            'ai.default'                   => $this->providerAgent,
            'ai.default_for_images'        => $this->providerImage,
            'ai.default_for_audio'         => $this->providerAudio,
            'ai.default_for_transcription' => $this->providerTranscription,
            'ai.default_for_embeddings'    => $this->providerEmbeddings,
            'ai.default_for_reranking'     => $this->providerReranking,
        ]);
    }

    // ── Send dispatcher ───────────────────────────────────────────────────────
    public function send(): void
    {
        $this->applyRuntimeConfig();

        match ($this->mode) {
            'agent'         => $this->runAgent(),
            'stream'        => $this->runStream(),
            'image'         => $this->runImage(),
            'audio'         => $this->runAudio(),
            'transcription' => $this->runTranscription(),
            'embeddings'    => $this->runEmbeddings(),
            'reranking'     => $this->runReranking(),
            default         => null,
        };
    }

    // ── Agent Chat ────────────────────────────────────────────────────────────
    private function runAgent(): void
    {
        $this->validate(['inputText' => 'required|string']);
        $userMsg = $this->inputText;
        $this->pushUser($userMsg, 'agent');
        $this->inputText = '';

        try {
            $response = (new PlaygroundAgent)->prompt(
                $userMsg,
                provider: $this->providerAgent,
                model: $this->modelAgent,
            );
            $this->messages[] = [
                'role'    => 'ai',
                'type'    => 'text',
                'content' => (string) $response,
                'meta'    => ['mode' => 'Agent Chat', 'provider' => $this->providerAgent, 'model' => $this->modelAgent],
            ];
        } catch (Throwable $e) {
            $this->pushError($e->getMessage());
        }
    }

    // ── Streaming ─────────────────────────────────────────────────────────────
    private function runStream(): void
    {
        $this->validate(['inputText' => 'required|string']);
        $userMsg = $this->inputText;
        $this->pushUser($userMsg, 'stream');
        $this->inputText = '';

        $this->messages[] = [
            'role'    => 'ai',
            'type'    => 'stream',
            'content' => '',
            'meta'    => ['mode' => 'Streaming', 'provider' => $this->providerStream, 'model' => $this->modelStream],
        ];
        $idx = array_key_last($this->messages);

        try {
            foreach ((new PlaygroundAgent)->stream(
                $userMsg,
                provider: $this->providerStream,
                model: $this->modelStream,
            ) as $chunk) {
                if (isset($chunk->text)) {
                    $this->stream(to: 'messages.' . $idx . '.content', content: $chunk->text, replace: false);
                }
            }
        } catch (Throwable $e) {
            $this->pushError($e->getMessage());
        }
    }

    // ── Image Generation ──────────────────────────────────────────────────────
    private function runImage(): void
    {
        $this->validate(['inputText' => 'required|string']);
        $prompt = $this->inputText;
        $this->pushUser($prompt, 'image');
        $this->inputText = '';

        try {
            $r   = Image::of($prompt)->generate();
            $src = method_exists($r, 'firstImage')
                ? 'data:' . $r->firstImage()->mime . ';base64,' . $r->firstImage()->image
                : 'data:image/png;base64,' . base64_encode((string) $r);

            $this->messages[] = [
                'role'    => 'ai',
                'type'    => 'image',
                'content' => $src,
                'meta'    => ['prompt' => $prompt, 'mode' => 'Image Gen', 'provider' => $this->providerImage, 'model' => $this->modelImage],
            ];
        } catch (Throwable $e) {
            $this->pushError($e->getMessage());
        }
    }

    // ── Text to Speech ────────────────────────────────────────────────────────
    private function runAudio(): void
    {
        $this->validate(['inputText' => 'required|string']);
        $prompt = $this->inputText;
        $this->pushUser($prompt, 'audio');
        $this->inputText = '';

        try {
            $r   = Audio::of($prompt)->generate();
            $src = method_exists($r, 'mimeType')
                ? 'data:' . ($r->mimeType() ?? 'audio/mp3') . ';base64,' . $r->audio
                : 'data:audio/mp3;base64,' . base64_encode((string) $r);

            $this->messages[] = [
                'role'    => 'ai',
                'type'    => 'audio',
                'content' => $src,
                'meta'    => ['prompt' => $prompt, 'mode' => 'Text to Speech', 'provider' => $this->providerAudio, 'model' => $this->modelAudio],
            ];
        } catch (Throwable $e) {
            $this->pushError($e->getMessage());
        }
    }

    // ── Transcription ─────────────────────────────────────────────────────────
    private function runTranscription(): void
    {
        $this->validate(['audioFile' => 'required|file']);
        $fileName         = $this->audioFile->getClientOriginalName();
        $this->messages[] = [
            'role'    => 'user',
            'type'    => 'file',
            'content' => $fileName,
            'meta'    => ['mode' => 'Transcription'],
        ];

        try {
            $path       = $this->audioFile->store('temp_audio', 'local');
            $transcript = Transcription::fromPath(storage_path('app/' . $path))->generate();
            $text       = isset($transcript->text) ? (string) $transcript->text : (string) $transcript;

            $this->messages[] = [
                'role'    => 'ai',
                'type'    => 'text',
                'content' => $text,
                'meta'    => ['mode' => 'Transcription', 'provider' => $this->providerTranscription, 'model' => $this->modelTranscription],
            ];
            $this->audioFile = null;
        } catch (Throwable $e) {
            $this->pushError($e->getMessage());
        }
    }

    // ── Embeddings ────────────────────────────────────────────────────────────
    private function runEmbeddings(): void
    {
        $this->validate(['inputText' => 'required|string']);
        $prompt = $this->inputText;
        $this->pushUser($prompt, 'embeddings');
        $this->inputText = '';

        try {
            $embeddings = Str::of($prompt)->toEmbeddings();
            $json       = json_encode(array_slice((array) $embeddings, 0, 10)) . '... (truncated)';

            $this->messages[] = [
                'role'    => 'ai',
                'type'    => 'code',
                'content' => $json,
                'meta'    => ['label' => 'Embedding Vectors (first 10 floats)', 'mode' => 'Embeddings', 'provider' => $this->providerEmbeddings, 'model' => $this->modelEmbeddings],
            ];
        } catch (Throwable $e) {
            $this->pushError($e->getMessage());
        }
    }

    // ── Reranking ─────────────────────────────────────────────────────────────
    private function runReranking(): void
    {
        $this->validate([
            'rerankingQuery'     => 'required|string',
            'rerankingDocuments' => 'required|string',
        ]);
        $query            = $this->rerankingQuery;
        $this->messages[] = [
            'role'    => 'user',
            'type'    => 'rerank_input',
            'content' => $query,
            'meta'    => ['documents' => $this->rerankingDocuments, 'mode' => 'Reranking'],
        ];

        try {
            $docs     = array_filter(array_map('trim', explode("\n", $this->rerankingDocuments)));
            $response = Reranking::of($docs)->rerank($query);
            $results  = [];
            foreach ($response as $item) {
                $results[] = $item->document ?? (string) $item;
            }
            $this->messages[] = [
                'role'    => 'ai',
                'type'    => 'reranking',
                'content' => $results,
                'meta'    => ['query' => $query, 'mode' => 'Reranking', 'provider' => $this->providerReranking, 'model' => $this->modelReranking],
            ];
        } catch (Throwable $e) {
            $this->pushError($e->getMessage());
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private function pushUser(string $content, string $mode): void
    {
        $modeLabels = [
            'agent'         => 'Agent Chat',
            'stream'        => 'Streaming',
            'image'         => 'Image Gen',
            'audio'         => 'Text to Speech',
            'transcription' => 'Transcription',
            'embeddings'    => 'Embeddings',
            'reranking'     => 'Reranking',
        ];
        $this->messages[] = [
            'role'    => 'user',
            'type'    => 'text',
            'content' => $content,
            'meta'    => ['mode' => $modeLabels[$mode] ?? $mode],
        ];
    }

    private function pushError(string $message): void
    {
        $this->messages[] = ['role' => 'ai', 'type' => 'error', 'content' => $message, 'meta' => []];
    }

    public function render()
    {
        return view('livewire.ai-playground')->layout('layouts.app');
    }
}
