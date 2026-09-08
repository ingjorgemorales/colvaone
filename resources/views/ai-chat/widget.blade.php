@php
    $aiChatUser = auth()->user();
    $aiChatUserInitials = collect([$aiChatUser?->name, $aiChatUser?->last_name])
        ->map(fn ($part) => mb_substr(trim((string) $part), 0, 1))
        ->filter()
        ->implode('');
    $aiChatUserInitials = mb_strtoupper($aiChatUserInitials !== '' ? $aiChatUserInitials : mb_substr((string) $aiChatUser?->email, 0, 1));
@endphp

<div
    x-data="aiChatWidget({
        bootstrapUrl: @js(route('ai-chat.bootstrap')),
        sendUrl: @js(route('ai-chat.messages.send')),
        clearUrl: @js(route('ai-chat.history.clear')),
        canClear: @js(auth()->user()->hasPermission('ai_chat.clear_own')),
        userInitials: @js($aiChatUserInitials),
    })"
    x-init="init()"
    class="ai-chat-widget"
>
    <style>
        .ai-chat-widget { position: fixed; right: 22px; bottom: 22px; z-index: 70; }
        .ai-chat-toggle { width: 54px; height: 54px; border-radius: 16px; border: 1px solid rgba(18,63,110,0.14); background: white; box-shadow: 0 14px 34px rgba(18,63,110,0.18); display: grid; place-items: center; cursor: pointer; transition: transform .2s, box-shadow .2s, border-color .2s; }
        .ai-chat-toggle:hover { transform: translateY(-2px); box-shadow: 0 18px 42px rgba(18,63,110,0.28); }
        .ai-chat-toggle img { width: 34px; height: 34px; object-fit: contain; filter: drop-shadow(0 1px 2px rgba(18,63,110,.16)); }
        .ai-chat-panel { position: absolute; right: 0; bottom: 68px; width: min(410px, calc(100vw - 28px)); height: min(620px, calc(100vh - 112px)); border-radius: 18px; background: rgba(255,255,255,.96); border: 1px solid rgba(18,63,110,.10); box-shadow: 0 22px 60px rgba(18,63,110,.18); overflow: hidden; display: flex; flex-direction: column; }
        .ai-chat-header { padding: 14px 16px; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid rgba(18,63,110,.08); background: rgba(255,255,255,.92); }
        .ai-chat-title { min-width: 0; flex: 1; }
        .ai-chat-title h3 { margin: 0; font-size: 14px; color: #1e293b; font-weight: 700; }
        .ai-chat-title p { margin: 2px 0 0; font-size: 11px; color: #94a3b8; }
        .ai-chat-icon-button { width: 32px; height: 32px; border-radius: 9px; border: 0; background: rgba(18,63,110,.05); color: #123f6e; display: grid; place-items: center; cursor: pointer; }
        .ai-chat-icon-button:hover { background: rgba(18,63,110,.10); }
        .ai-chat-body { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 12px; }
        .ai-chat-message-wrap { max-width: 86%; display: flex; flex-direction: column; gap: 4px; }
        .ai-chat-message-wrap.user { align-self: flex-end; align-items: flex-end; }
        .ai-chat-message-wrap.assistant { align-self: flex-start; align-items: flex-start; }
        .ai-chat-message { width: fit-content; max-width: 100%; border-radius: 14px; padding: 9px 11px; font-size: 12px; line-height: 1.45; white-space: pre-wrap; word-break: break-word; display: flex; align-items: flex-start; gap: 9px; text-align: left; }
        .ai-chat-message.user { background: linear-gradient(135deg,#123f6e,#1d5f99); color: white; border-bottom-right-radius: 5px; flex-direction: row-reverse; }
        .ai-chat-message.assistant { background: rgba(18,63,110,.06); color: #1e293b; border-bottom-left-radius: 5px; }
        .ai-chat-message-text { min-width: 0; }
        .ai-chat-badge { width: 24px; height: 24px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; flex: 0 0 24px; font-size: 9px; font-weight: 800; letter-spacing: 0; line-height: 1; }
        .ai-chat-message.user .ai-chat-badge { background: rgba(255,255,255,.18); color: white; }
        .ai-chat-message.assistant .ai-chat-badge { background: white; color: #123f6e; border: 1px solid rgba(18,63,110,.10); }
        .ai-chat-time { font-size: 10px; color: #94a3b8; padding: 0 4px; line-height: 1.2; }
        .ai-chat-typing { display: inline-flex; align-items: center; gap: 4px; min-width: 34px; padding-top: 5px; }
        .ai-chat-typing span { width: 6px; height: 6px; border-radius: 50%; background: #8aa0bb; opacity: .38; animation: ai-chat-bounce 1s infinite ease-in-out; }
        .ai-chat-typing span:nth-child(2) { animation-delay: .16s; }
        .ai-chat-typing span:nth-child(3) { animation-delay: .32s; }
        @keyframes ai-chat-bounce { 0%, 80%, 100% { transform: translateY(0); opacity: .35; } 40% { transform: translateY(-4px); opacity: 1; } }
        .ai-chat-empty { margin: auto; text-align: center; color: #94a3b8; max-width: 280px; }
        .ai-chat-empty i { width: 32px; height: 32px; color: #123f6e; margin-bottom: 10px; }
        .ai-chat-empty p { margin: 0; font-size: 13px; line-height: 1.45; }
        .ai-chat-error { margin: 0 16px 12px; padding: 9px 11px; border-radius: 10px; background: rgba(239,68,68,.08); color: #dc2626; font-size: 12px; border: 1px solid rgba(239,68,68,.14); }
        .ai-chat-form { padding: 12px; display: flex; gap: 8px; border-top: 1px solid rgba(18,63,110,.08); background: rgba(255,255,255,.94); }
        .ai-chat-form textarea { flex: 1; min-height: 42px; max-height: 110px; resize: none; border-radius: 12px; border: 1px solid rgba(18,63,110,.12); padding: 10px 12px; font-size: 12px; outline: none; color: #1e293b; background: white; }
        .ai-chat-form textarea:focus { border-color: rgba(18,63,110,.34); box-shadow: 0 0 0 3px rgba(18,63,110,.06); }
        .ai-chat-send { width: 42px; height: 42px; border-radius: 12px; border: 0; display: grid; place-items: center; background: #123f6e; color: white; cursor: pointer; flex-shrink: 0; }
        .ai-chat-send:disabled { opacity: .55; cursor: not-allowed; }
        @media (max-width: 560px) {
            .ai-chat-widget { right: 14px; bottom: 14px; }
            .ai-chat-panel { right: -4px; bottom: 64px; height: min(560px, calc(100vh - 92px)); }
        }
    </style>

    <div x-cloak x-show="open" x-transition class="ai-chat-panel" @click.outside="open = false">
        <div class="ai-chat-header">
            <div style="width:36px;height:36px;border-radius:11px;background:white;border:1px solid rgba(18,63,110,.12);display:grid;place-items:center;flex-shrink:0">
                <img src="{{ asset('images/logo_icono.png') }}" alt="ColvaOne" style="width:27px;height:27px;object-fit:contain">
            </div>
            <div class="ai-chat-title">
                <h3>Chat IA</h3>
                <p x-text="configured ? 'Asistente interno de ColvaOne' : 'Pendiente de configuracion'"></p>
            </div>
            <button x-show="canClear && messages.length > 0" type="button" class="ai-chat-icon-button" @click="clearHistory()" title="Limpiar historial">
                <i data-lucide="trash-2" style="width:15px;height:15px"></i>
            </button>
            <button type="button" class="ai-chat-icon-button" @click="open = false" title="Cerrar">
                <i data-lucide="x" style="width:16px;height:16px"></i>
            </button>
        </div>

        <div class="ai-chat-body" x-ref="messages">
            <template x-if="loading">
                <div class="ai-chat-empty">
                    <i data-lucide="loader-circle" style="animation:spin 1s linear infinite"></i>
                    <p>Cargando historial...</p>
                </div>
            </template>

            <template x-if="!loading && !configured && messages.length === 0">
                <div class="ai-chat-empty">
                    <i data-lucide="sparkles"></i>
                    <p>Pregunta por tareas, indicadores, aplicativos, usuarios o comites segun tus permisos.</p>
                </div>
            </template>

            <template x-if="!loading && configured && messages.length === 0">
                <div class="ai-chat-message-wrap assistant">
                    <div class="ai-chat-message assistant">
                        <span class="ai-chat-badge">IA</span>
                        <span class="ai-chat-message-text">Hola, soy el asistente de ColvaOne. Estoy listo para ayudarte con consultas de la aplicacion segun tus permisos.</span>
                    </div>
                </div>
            </template>

            <template x-for="message in messages" :key="message.id">
                <div class="ai-chat-message-wrap" :class="message.role">
                    <div class="ai-chat-message" :class="message.role">
                        <span class="ai-chat-badge" x-text="message.role === 'user' ? userInitials : 'IA'"></span>
                        <span class="ai-chat-message-text" x-text="message.content"></span>
                    </div>
                    <span class="ai-chat-time" x-show="message.created_at" x-text="message.created_at"></span>
                </div>
            </template>

            <template x-if="sending">
                <div class="ai-chat-message-wrap assistant">
                    <div class="ai-chat-message assistant">
                        <span class="ai-chat-badge">IA</span>
                        <span class="ai-chat-typing" aria-label="La IA esta escribiendo">
                            <span></span><span></span><span></span>
                        </span>
                    </div>
                </div>
            </template>
        </div>

        <p x-cloak x-show="error" class="ai-chat-error" x-text="error"></p>

        <form class="ai-chat-form" @submit.prevent="sendMessage()">
            <textarea x-model="message" :disabled="sending || !configured" maxlength="2000" placeholder="Escribe tu pregunta..." @keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); sendMessage(); }"></textarea>
            <button type="submit" class="ai-chat-send" :disabled="sending || !configured || message.trim() === ''" title="Enviar">
                <i data-lucide="send" style="width:16px;height:16px"></i>
            </button>
        </form>
    </div>

    <button type="button" class="ai-chat-toggle" @click="toggle()" title="Chat IA">
        <img src="{{ asset('images/logo_icono.png') }}" alt="Chat IA">
    </button>
</div>

<script>
    window.aiChatWidget = function(config) {
        return {
            open: false,
            loading: false,
            sending: false,
            booted: false,
            configured: false,
            canClear: config.canClear,
            userInitials: config.userInitials || 'U',
            message: '',
            error: '',
            messages: [],
            init() {
                this.$nextTick(() => lucide.createIcons());
            },
            async toggle() {
                this.open = !this.open;
                if (this.open && !this.booted) {
                    await this.load();
                }
                this.$nextTick(() => {
                    lucide.createIcons();
                    this.scrollBottom();
                });
            },
            async load() {
                this.loading = true;
                this.error = '';

                try {
                    const response = await fetch(config.bootstrapUrl, {
                        headers: { 'Accept': 'application/json' },
                    });
                    const data = await response.json();
                    this.configured = Boolean(data.configured);
                    this.messages = data.messages || [];
                    this.error = this.configured ? '' : (data.message || 'El chat IA no esta configurado.');
                    this.booted = true;
                } catch (error) {
                    this.error = 'No se pudo cargar el chat IA.';
                } finally {
                    this.loading = false;
                    this.$nextTick(() => {
                        lucide.createIcons();
                        this.scrollBottom();
                    });
                }
            },
            async sendMessage() {
                const content = this.message.trim();
                if (!content || this.sending || !this.configured) return;

                this.sending = true;
                this.error = '';
                this.message = '';
                this.messages = [
                    ...this.messages,
                    {
                        id: `pending-${Date.now()}`,
                        role: 'user',
                        content,
                        created_at: this.nowInBogota(),
                    },
                ];
                this.$nextTick(() => this.scrollBottom());

                try {
                    const response = await fetch(config.sendUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                        body: JSON.stringify({ message: content }),
                    });
                    const data = await response.json();
                    this.messages = data.messages || this.messages;

                    if (!response.ok) {
                        this.error = data.message || 'No se pudo responder la pregunta.';
                    }
                } catch (error) {
                    this.error = 'No se pudo conectar con el chat IA.';
                } finally {
                    this.sending = false;
                    this.$nextTick(() => {
                        lucide.createIcons();
                        this.scrollBottom();
                    });
                }
            },
            async clearHistory() {
                if (!confirm('Limpiar tu historial del chat IA?')) return;

                try {
                    const response = await fetch(config.clearUrl, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                    });
                    const data = await response.json();
                    this.messages = data.messages || [];
                    this.error = '';
                } catch (error) {
                    this.error = 'No se pudo limpiar el historial.';
                }
            },
            scrollBottom() {
                if (this.$refs.messages) {
                    this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight;
                }
            },
            nowInBogota() {
                const parts = new Intl.DateTimeFormat('es-CO', {
                    timeZone: 'America/Bogota',
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false,
                }).formatToParts(new Date()).reduce((carry, part) => {
                    carry[part.type] = part.value;
                    return carry;
                }, {});

                return `${parts.day}/${parts.month}/${parts.year} ${parts.hour}:${parts.minute}`;
            },
        };
    };
</script>
