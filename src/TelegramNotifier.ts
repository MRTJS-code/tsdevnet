export class TelegramNotifier {
  static async sendMessage(text: string): Promise<void> {
    const token = process.env.TELEGRAM_BOT_TOKEN;
    const chatId = process.env.OWNER_TELEGRAM_CHAT_ID;

    if (!token || !chatId) {
      console.warn("Telegram not configured; skipping message");
      return;
    }

    const url = `https://api.telegram.org/bot${token}/sendMessage`;
    try {
      const resp = await fetch(url, {
        method: "POST",
        headers: { "content-type": "application/json" },
        body: JSON.stringify({ chat_id: chatId, text })
      });

      if (!resp.ok) {
        const body = await resp.text();
        console.warn("Telegram send failed", resp.status, body);
      }
    } catch (err) {
      console.warn("Telegram send error", err);
    }
  }
}
