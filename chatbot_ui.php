<!-- Chatbot Styles (CSS) -->
<style>
    .chatbot-btn { position: fixed; bottom: 20px; right: 20px; background: #ff4747; color: #fff; border: none; border-radius: 50%; width: 60px; height: 60px; font-size: 24px; cursor: pointer; box-shadow: 0 4px 15px rgba(255, 71, 71, 0.4); z-index: 9999; transition: 0.3s; }
    .chatbot-btn:hover { transform: scale(1.1); }
    .chat-window { position: fixed; bottom: 90px; right: 20px; width: 320px; background: #1c1c1c; border-radius: 10px; border: 1px solid #333; box-shadow: 0 5px 20px rgba(0,0,0,0.5); display: none; flex-direction: column; overflow: hidden; z-index: 9999; }
    .chat-header { background: #222; padding: 15px; border-bottom: 1px solid #333; display: flex; justify-content: space-between; align-items: center; color: #fff; font-weight: bold; }
    .close-chat { cursor: pointer; color: #ff4747; font-size: 18px; font-weight: bold; }
    .chat-body { padding: 15px; height: 300px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; background: #121212; }
    .message { max-width: 80%; padding: 10px 15px; border-radius: 15px; font-size: 14px; line-height: 1.4; }
    .user-msg { background: #ff4747; color: #fff; align-self: flex-end; border-bottom-right-radius: 2px; }
    .bot-msg { background: #333; color: #fff; align-self: flex-start; border-bottom-left-radius: 2px; }
    .chat-footer { padding: 10px; background: #222; border-top: 1px solid #333; display: flex; }
    .chat-input { flex: 1; padding: 10px; border: none; border-radius: 20px; background: #121212; color: #fff; outline: none; padding-left: 15px; border: 1px solid #444; }
    .send-btn { background: none; border: none; color: #ff4747; font-size: 20px; cursor: pointer; margin-left: 10px; }
</style>

<!-- Chatbot Markup (HTML) -->
<button class="chatbot-btn" onclick="toggleChat()">💬</button>

<div class="chat-window" id="chatWindow">
    <div class="chat-header">
        <span>⚡ Volt AI Assistant</span>
        <span class="close-chat" onclick="toggleChat()">✕</span>
    </div>
    <div class="chat-body" id="chatBody">
        <div class="message bot-msg">Hi! I am Volt AI. How can I assist you with our products today?</div>
    </div>
    <div class="chat-footer">
        <input type="text" id="chatInput" class="chat-input" placeholder="Ask about watches, earbuds..." onkeypress="handleEnter(event)">
        <button class="send-btn" onclick="sendMessage()">➤</button>
    </div>
</div>

<!-- Chatbot Client Script (AJAX) -->
<script>
    function toggleChat() {
        let chat = document.getElementById('chatWindow');
        chat.style.display = (chat.style.display === 'flex') ? 'none' : 'flex';
    }

    function handleEnter(e) {
        if (e.key === 'Enter') sendMessage();
    }

    function sendMessage() {
        let inputField = document.getElementById('chatInput');
        let message = inputField.value.trim();
        if (message === '') return;

        // Append user message to chat UI
        appendMessage(message, 'user-msg');
        inputField.value = '';

        // Dispatch message to chatbot_api.php via AJAX
        fetch('chatbot_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'message=' + encodeURIComponent(message)
        })
        .then(response => response.json())
        .then(data => {
            // Append bot reply to chat UI
            setTimeout(() => appendMessage(data.reply, 'bot-msg'), 500);
        })
        .catch(error => console.error('Error:', error));
    }

    function appendMessage(text, className) {
        let chatBody = document.getElementById('chatBody');
        let msgDiv = document.createElement('div');
        msgDiv.className = 'message ' + className;
        msgDiv.innerHTML = text; // Using innerHTML to support markdown formatting like bold text
        chatBody.appendChild(msgDiv);
        chatBody.scrollTop = chatBody.scrollHeight; // Auto-scroll chat body to bottom
    }
</script>