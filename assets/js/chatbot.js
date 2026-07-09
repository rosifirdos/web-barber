/**
 * IF Barber — Chatbot Frontend Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    const chatbotToggle = document.getElementById('chatbotToggle');
    const chatbotWindow = document.getElementById('chatbotWindow');
    const chatbotClose = document.getElementById('chatbotClose');
    const chatbotForm = document.getElementById('chatbotForm');
    const chatbotInput = document.getElementById('chatbotInput');
    const chatbotMessages = document.getElementById('chatbotMessages');

    if (!chatbotToggle || !chatbotWindow) return;

    // Toggle Chatbot Window
    chatbotToggle.addEventListener('click', () => {
        chatbotWindow.classList.add('active');
        chatbotToggle.style.transform = 'scale(0)';
        setTimeout(() => chatbotInput.focus(), 300);
    });

    // Close Chatbot Window
    chatbotClose.addEventListener('click', () => {
        chatbotWindow.classList.remove('active');
        chatbotToggle.style.transform = 'scale(1)';
    });

    // Handle Form Submit
    chatbotForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const message = chatbotInput.value.trim();
        if (!message) return;

        // Clear input
        chatbotInput.value = '';

        // Add User Message to UI
        addMessage(message, 'user');

        // Show typing indicator
        const typingId = showTypingIndicator();

        try {
            // Send request to backend
            const response = await fetch(BASE_URL + '/api/chatbot.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message })
            });

            const data = await response.json();
            
            // Remove typing indicator
            removeTypingIndicator(typingId);

            if (data.status === 'success') {
                addMessage(data.reply, 'bot');
            } else {
                addMessage('Mohon maaf, ' + data.message, 'bot');
            }
        } catch (error) {
            removeTypingIndicator(typingId);
            addMessage('Terjadi kesalahan koneksi. Coba beberapa saat lagi.', 'bot');
            console.error('Chatbot Error:', error);
        }
    });

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function addMessage(text, sender) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `chatbot-msg chatbot-msg--${sender}`;
        
        const bubble = document.createElement('div');
        bubble.className = 'chatbot-bubble';
        
        // Convert basic markdown/newlines safely after escaping HTML
        bubble.innerHTML = escapeHtml(text).replace(/\n/g, '<br>');
        
        msgDiv.appendChild(bubble);
        chatbotMessages.appendChild(msgDiv);
        
        // Scroll to bottom
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    function showTypingIndicator() {
        const id = 'typing-' + Date.now();
        const msgDiv = document.createElement('div');
        msgDiv.className = `chatbot-msg chatbot-msg--bot`;
        msgDiv.id = id;
        
        const bubble = document.createElement('div');
        bubble.className = 'chatbot-bubble chatbot-typing';
        bubble.innerHTML = '<span></span><span></span><span></span>';
        
        msgDiv.appendChild(bubble);
        chatbotMessages.appendChild(msgDiv);
        
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        return id;
    }

    function removeTypingIndicator(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }
});
