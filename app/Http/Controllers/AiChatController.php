<?php

namespace App\Http\Controllers;

use App\Models\AiChatConversation;
use App\Models\AiChatSetting;
use App\Services\AiChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class AiChatController extends Controller
{
    public function bootstrap(Request $request): JsonResponse
    {
        if (!$this->tablesReady()) {
            return response()->json([
                'configured' => false,
                'message' => 'Falta ejecutar la migracion del chat IA en esta base de datos.',
                'conversation_id' => null,
                'messages' => [],
            ]);
        }

        $settings = AiChatSetting::current();
        $conversation = $this->conversation($request);

        return response()->json([
            'configured' => $settings->is_active && $settings->hasApiKey(),
            'message' => $settings->is_active
                ? 'Chat IA listo.'
                : 'El chat IA esta inactivo. Pide a un administrador que lo configure.',
            'conversation_id' => $conversation->id,
            'messages' => $this->messages($conversation),
        ]);
    }

    public function send(Request $request, AiChatService $chat): JsonResponse
    {
        if (!$this->tablesReady()) {
            return response()->json(['message' => 'Falta ejecutar la migracion del chat IA en esta base de datos.'], 422);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $content = trim($validated['message']);

        if ($content === '') {
            return response()->json(['message' => 'Escribe una pregunta para el chat IA.'], 422);
        }

        $settings = AiChatSetting::current();
        if (!$settings->is_active || !$settings->hasApiKey()) {
            return response()->json(['message' => 'El chat IA no esta configurado o esta inactivo.'], 422);
        }

        $conversation = $this->conversation($request);

        $conversation->messages()->create([
            'role' => 'user',
            'content' => $content,
        ]);

        if (!$conversation->title) {
            $conversation->update(['title' => Str::limit($content, 80)]);
        } else {
            $conversation->touch();
        }

        try {
            $answer = $chat->answer($request->user(), $conversation, $content);
        } catch (Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'messages' => $this->messages($conversation),
            ], 422);
        }

        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $answer,
        ]);
        $conversation->touch();

        return response()->json([
            'messages' => $this->messages($conversation),
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        if (!$this->tablesReady()) {
            return response()->json([
                'conversation_id' => null,
                'messages' => [],
            ]);
        }

        AiChatConversation::query()
            ->where('user_id', $request->user()->id)
            ->delete();

        $conversation = $this->conversation($request);

        return response()->json([
            'conversation_id' => $conversation->id,
            'messages' => [],
        ]);
    }

    private function conversation(Request $request): AiChatConversation
    {
        return AiChatConversation::query()
            ->where('user_id', $request->user()->id)
            ->latest('updated_at')
            ->first()
            ?? AiChatConversation::create([
                'user_id' => $request->user()->id,
                'title' => 'Chat IA',
            ]);
    }

    private function messages(AiChatConversation $conversation): array
    {
        return $conversation->messages()
            ->reorder()
            ->latest()
            ->latest('id')
            ->take(30)
            ->get()
            ->reverse()
            ->values()
            ->map(fn ($message) => [
                'id' => $message->id,
                'role' => $message->role,
                'content' => $message->content,
                'created_at' => $message->created_at
                    ?->timezone(config('app.timezone'))
                    ->format('d/m/Y H:i'),
            ])
            ->all();
    }

    private function tablesReady(): bool
    {
        return Schema::hasTable('ai_chat_settings')
            && Schema::hasTable('ai_chat_conversations')
            && Schema::hasTable('ai_chat_messages');
    }
}
