<script>
const echo = new Echo({
    broadcaster: 'pusher',
    key: '{{ config("broadcasting.connections.pusher.key") }}',
    cluster: 'ap1', encrypted: true,
});

echo.private(`chat.{{ Auth::id() }}`)
    .listen('MessageSent', (e) => {
        appendMessage(e.message);
    });

function appendMessage(msg) {
    const div = document.createElement('div');
    div.className = 'flex gap-2 p-3 rounded-xl bg-blue-50';
    div.innerHTML = `<p class="text-sm">${msg.pesan}</p>`;
    document.getElementById('chat-messages').appendChild(div);
}
</script>