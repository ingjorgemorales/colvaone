<?php

namespace App\Services;

use App\Models\AiChatConversation;
use App\Models\AiChatSetting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class AiChatService
{
    public function __construct(
        private readonly AiChatContextService $contextService,
    ) {
    }

    public function answer(User $user, AiChatConversation $conversation, string $question): string
    {
        $settings = AiChatSetting::current();

        if (!$settings->is_active) {
            throw new RuntimeException('El chat IA esta inactivo.');
        }

        if (!$settings->hasApiKey()) {
            throw new RuntimeException('El chat IA no tiene API key configurada.');
        }

        $history = $conversation->messages()
            ->latest()
            ->take($settings->max_context_messages)
            ->get()
            ->reverse()
            ->values();

        if ($history->last()?->role === 'user' && trim($history->last()->content) === trim($question)) {
            $history->pop();
        }

        $context = $this->contextService->build($user, $question);
        $instructions = $this->instructions($settings);
        $endpoint = $settings->endpoint ?: 'https://api.openai.com/v1/responses';

        $payload = Str::contains($endpoint, '/chat/completions')
            ? $this->chatCompletionsPayload($settings, $instructions, $context, $history, $question)
            : $this->responsesPayload($settings, $instructions, $context, $history, $question);

        $response = Http::withToken($settings->api_key)
            ->acceptJson()
            ->asJson()
            ->timeout(60)
            ->post($endpoint, $payload);

        if (!$response->successful()) {
            $message = $response->json('error.message') ?: 'No se pudo obtener respuesta del proveedor IA.';
            throw new RuntimeException($message);
        }

        return $this->extractText($response->json());
    }

    private function instructions(AiChatSetting $settings): string
    {
        $customPrompt = trim((string) $settings->system_prompt);

        return trim(($customPrompt !== '' ? $customPrompt . "\n\n" : '') . <<<PROMPT
Eres el asistente interno de ColvaOne. Responde en espanol, claro y breve.
Usa solo el contexto autorizado que entrega la aplicacion. Si el contexto no contiene la respuesta, dilo y sugiere donde consultarlo dentro del sistema.
No inventes datos, URLs, nombres, correos, indicadores, tareas, estados ni permisos.
Respeta que cada usuario solo puede consultar informacion permitida por sus roles.
Responde de forma natural y concreta, normalmente en 1 a 4 frases.
Si el usuario solo saluda, saluda de vuelta y pregunta en que puedes ayudar; no resumas tareas ni modulos si no te lo pidieron.
No repitas el listado completo de capacidades en cada respuesta. Mencionalo solo si el usuario pregunta que puedes hacer.
Si el usuario escribe con errores o abreviaturas, interpreta la intencion probable y responde sin corregirlo de forma innecesaria.
Contesta siempre la pregunta actual del usuario, no el mensaje anterior del historial.
PROMPT);
    }

    private function responsesPayload(AiChatSetting $settings, string $instructions, string $context, $history, string $question): array
    {
        return [
            'model' => $settings->model,
            'instructions' => $instructions,
            'input' => $this->plainConversation($context, $history, $question),
        ];
    }

    private function chatCompletionsPayload(AiChatSetting $settings, string $instructions, string $context, $history, string $question): array
    {
        $messages = [
            ['role' => 'system', 'content' => $instructions],
            ['role' => 'system', 'content' => "Contexto autorizado de ColvaOne:\n" . $context],
        ];

        foreach ($history as $message) {
            if (!in_array($message->role, ['user', 'assistant'], true)) {
                continue;
            }

            $messages[] = [
                'role' => $message->role,
                'content' => $message->content,
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $question,
        ];

        return [
            'model' => $settings->model,
            'messages' => $messages,
        ];
    }

    private function plainConversation(string $context, $history, string $question): string
    {
        $lines = [
            "Contexto autorizado de ColvaOne:\n{$context}",
            'Historial reciente:',
        ];

        foreach ($history as $message) {
            if (!in_array($message->role, ['user', 'assistant'], true)) {
                continue;
            }

            $label = $message->role === 'user' ? 'Usuario' : 'Asistente';
            $lines[] = "{$label}: {$message->content}";
        }

        $lines[] = "Pregunta actual del usuario:\n{$question}";

        return implode("\n\n", $lines);
    }

    private function extractText(array $data): string
    {
        if (filled($data['output_text'] ?? null)) {
            return trim((string) $data['output_text']);
        }

        $choiceText = $data['choices'][0]['message']['content'] ?? null;
        if (filled($choiceText)) {
            return trim((string) $choiceText);
        }

        $chunks = [];
        foreach (($data['output'] ?? []) as $output) {
            foreach (($output['content'] ?? []) as $content) {
                if (filled($content['text'] ?? null)) {
                    $chunks[] = $content['text'];
                }
            }
        }

        $text = trim(implode("\n", $chunks));

        if ($text === '') {
            throw new RuntimeException('La IA respondio sin texto util.');
        }

        return $text;
    }
}
