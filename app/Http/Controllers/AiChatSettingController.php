<?php

namespace App\Http\Controllers;

use App\Models\AiChatSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AiChatSettingController extends Controller
{
    public function edit(): View
    {
        $settings = AiChatSetting::current();

        return view('ai-chat.settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $settings = AiChatSetting::current();

        $validated = $request->validate([
            'provider' => ['required', 'string', 'max:40'],
            'endpoint' => ['required', 'url:http,https', 'max:255'],
            'model' => ['required', 'string', 'max:120'],
            'api_key' => [$settings->hasApiKey() ? 'nullable' : 'required', 'string', 'max:500'],
            'system_prompt' => ['nullable', 'string'],
            'max_context_messages' => ['required', 'integer', 'min:4', 'max:30'],
        ]);

        $data = [
            'provider' => $validated['provider'],
            'endpoint' => $validated['endpoint'],
            'model' => $validated['model'],
            'system_prompt' => $validated['system_prompt'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'max_context_messages' => $validated['max_context_messages'],
            'updated_by' => Auth::id(),
        ];

        if ($request->filled('api_key')) {
            $data['api_key'] = $validated['api_key'];
        }

        $settings->update($data);

        return redirect()->route('ai-chat.settings.edit')
            ->with('success', 'Configuracion del chat IA actualizada correctamente.');
    }
}
