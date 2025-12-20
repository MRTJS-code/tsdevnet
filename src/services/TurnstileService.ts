import { config } from "../config";

export class TurnstileService {
  static async verify(responseToken: string, remoteip?: string): Promise<boolean> {
    if (!config.turnstile.secretKey) {
      return config.env === "dev"; // fail-open only in dev
    }

    const params = new URLSearchParams();
    params.append("secret", config.turnstile.secretKey);
    params.append("response", responseToken);
    if (remoteip) params.append("remoteip", remoteip);

    const resp = await fetch(
      "https://challenges.cloudflare.com/turnstile/v0/siteverify",
      {
        method: "POST",
        headers: { "content-type": "application/x-www-form-urlencoded" },
        body: params
      }
    );

    const data = (await resp.json()) as { success: boolean };
    return !!data.success;
  }
}
