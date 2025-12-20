import nodemailer from "nodemailer";
import { config } from "../config";

export type MailOptions = {
  to: string;
  subject: string;
  text: string;
  html?: string;
};

export class MailService {
  private static getTransport() {
    return nodemailer.createTransport({
      host: config.smtp.host,
      port: config.smtp.port,
      secure: config.smtp.port === 465,
      auth:
        config.smtp.user && config.smtp.pass
          ? { user: config.smtp.user, pass: config.smtp.pass }
          : undefined
    });
  }

  static async sendMail({ to, subject, text, html }: MailOptions) {
    if (config.env === "dev" && config.devMailbox === "console") {
      console.log(`DEV_MAILBOX -> ${to}: ${subject}\n${text}`);
      return;
    }

    if (!config.smtp.host) {
      console.log(`SMTP not configured. Skipping email to ${to}: ${subject}`);
      return;
    }

    const transporter = this.getTransport();
    await transporter.sendMail({
      from: `${config.smtp.fromName} <${config.smtp.fromEmail}>`,
      to,
      subject,
      text,
      html
    });
  }
}
