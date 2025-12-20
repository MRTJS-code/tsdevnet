export type ChatTier = "demo" | "full";
export type ChatMessage = { sender: "user" | "assistant"; content: string };

export class ChatService {
  static async generateReply(
    messages: ChatMessage[],
    tier: ChatTier
  ): Promise<string> {
    const latestUser = [...messages]
      .reverse()
      .find((m) => m.sender === "user")?.content.toLowerCase();

    if (latestUser?.includes("role")) {
      return "I'm Tony Smith, a technical recruiter partnering with founders to fill critical engineering roles quickly.";
    }

    if (latestUser?.includes("skills")) {
      return "Typical hires cover: frontend (TypeScript/React), backend (Node/Go), data (Python/SQL), and cloud (AWS/GCP).";
    }

    const limits = tier === "demo" ? 5 : 50;
    return `Thanks for reaching out! I can outline process, timelines, and sample profiles. Want me to set up a quick call? You have ${limits} messages/day in this tier.`;
  }

  // Placeholder for future Azure OpenAI / Foundry integration
  static async generateLLMReply(messages: ChatMessage[], tier: ChatTier) {
    throw new Error("Not implemented: wire this to Azure OpenAI/Foundry and keep logging + limits.");
  }
}
