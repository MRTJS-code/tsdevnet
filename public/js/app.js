const form = document.getElementById("chat-form");
const chat = document.getElementById("chat");
const chatPanel = document.querySelector(".chat-panel");
const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
let conversationId = chatPanel?.dataset.conversationId || "";
let lastTs = chatPanel?.dataset.last || "";
let pollDelay = 2500;

function addBubble(sender, text, isOwnerReply) {
  if (!chat) return;
  const div = document.createElement("div");
  div.className = `bubble ${sender}`;
  div.textContent = (isOwnerReply ? "Tony: " : "") + text;
  chat.appendChild(div);
  chat.scrollTop = chat.scrollHeight;
}

async function pollMessages() {
  if (!conversationId) return setTimeout(pollMessages, pollDelay);
  try {
    const url =
      `/api/conversations/${conversationId}/messages` +
      (lastTs ? `?after=${encodeURIComponent(lastTs)}` : "");
    const res = await fetch(url);
    if (res.ok) {
      const data = await res.json();
      if (data.messages && data.messages.length) {
        data.messages.forEach((m) => {
          addBubble(m.sender, m.content, m.isOwnerReply);
        });
        lastTs = data.messages[data.messages.length - 1].createdAt;
      }
      pollDelay = 2500;
    } else {
      pollDelay = Math.min(pollDelay + 1000, 8000);
    }
  } catch (e) {
    pollDelay = Math.min(pollDelay + 1000, 8000);
  }
  setTimeout(pollMessages, pollDelay);
}

if (form && chat) {
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const textarea = form.querySelector("textarea");
    const message = textarea?.value.trim();
    if (!message) return;

    textarea.value = "";
    addBubble("user", message);

    try {
      const res = await fetch("/api/chat", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "CSRF-Token": csrf
        },
        body: JSON.stringify({ message })
      });

      const data = await res.json();
      if (!res.ok) {
        addBubble("assistant", data.error || "Something went wrong");
        return;
      }

      addBubble("assistant", data.reply);
      const remaining = document.querySelector("#chat")?.dataset;
      if (remaining) remaining.remaining = data.remaining;
      if (data.conversationId) conversationId = data.conversationId;
    } catch (err) {
      addBubble("assistant", "Network error. Please retry.");
    }
  });
  pollMessages();
}

const handoffForm = document.getElementById("handoff-form");
if (handoffForm) {
  handoffForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const textarea = handoffForm.querySelector("textarea");
    const message = textarea?.value.trim();
    if (!message) return;
    try {
      const res = await fetch("/api/handoff", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "CSRF-Token": csrf
        },
        body: JSON.stringify({ message, type: "live_chat" })
      });
      const data = await res.json();
      if (res.ok) {
        addBubble("assistant", data.message || "Tony has been notified.");
        textarea.value = "";
        if (data.conversationId) conversationId = data.conversationId;
      } else {
        addBubble("assistant", data.error || "Unable to send request right now.");
      }
    } catch (err) {
      addBubble("assistant", "Network error. Please retry.");
    }
  });
}
