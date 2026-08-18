import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  title: "UniGuide — найди свой университет",
  description: "Требования, стоимость, статистика поступления и сравнение университетов мира.",
  icons: {
    icon: "/favicon.svg",
    shortcut: "/favicon.svg",
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="ru">
      <body>{children}</body>
    </html>
  );
}
