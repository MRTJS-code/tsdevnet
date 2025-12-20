import { Prisma } from "@prisma/client";
import { config } from "../config";
import { MailService } from "./MailService";
import { TelegramNotifier } from "../TelegramNotifier";

export class NotificationService {
  static async notifyOwnerNewSignup(user: Prisma.UserGetPayload<{}>) {
    const subject = `New signup: ${user.name || user.email}`;
    const text = `A new user signed up.\nName: ${user.name}\nEmail: ${user.email}\nCompany: ${user.company || ""}\nRole: ${user.roleType || ""}\nStatus: ${user.status}`;

    if (config.ownerEmail) {
      await MailService.sendMail({ to: config.ownerEmail, subject, text });
    }
    const adminLink = `${config.publicBaseUrl}/admin/users/${user.id}`;
    await TelegramNotifier.sendMessage(
      `New access request: ${user.name || user.email} (${user.email}) @ ${user.company || "N/A"} - status: pending\n${adminLink}`
    );
  }

  static async notifyOwnerHandoffRequest(
    request: Prisma.HandoffRequestGetPayload<{}>,
    user: Prisma.UserGetPayload<{}>
  ) {
    const subject = `Handoff request from ${user.email}`;
    const text = `User ${user.name || user.email} requested: ${request.type}\nMessage: ${request.message || "(none)"}\nStatus: ${request.status}`;

    if (config.ownerEmail) {
      await MailService.sendMail({ to: config.ownerEmail, subject, text });
    }
    const adminChatLink = request.conversationId
      ? `${config.publicBaseUrl}/admin/chat/${request.conversationId}`
      : `${config.publicBaseUrl}/admin`;
    await TelegramNotifier.sendMessage(
      `New chat request from ${user.name || user.email} (${user.company || "N/A"}): ${request.message || "(none)"}\n${adminChatLink}`
    );
  }
}
