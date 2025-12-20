document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('chat-form');
    const input = document.getElementById('chat-input');
    const win = document.getElementById('chat-window');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    if (!form) return;

    const appendMessage = (text, sender) => {
        const div = document.createElement('div');
        div.className = 'message ' + sender;
        div.textContent = text;
        win.appendChild(div);
        win.scrollTop = win.scrollHeight;
    };

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const message = input.value.trim();
        if (!message) return;
        appendMessage(message, 'user');
        input.value = '';
        try {
            const res = await fetch('/app/chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrf
                },
                body: JSON.stringify({ message })
            });
            const data = await res.json();
            if (!res.ok) {
                appendMessage(data.error || 'Error', 'assistant');
                return;
            }
            appendMessage(data.reply, 'assistant');
            if (typeof data.remaining !== 'undefined') {
                appendMessage(`Remaining today: ${data.remaining} (${data.tier})`, 'system');
            }
        } catch (err) {
            appendMessage('Network error. Please retry.', 'assistant');
        }
    });
});
