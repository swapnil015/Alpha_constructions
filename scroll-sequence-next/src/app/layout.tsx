import type { Metadata, Viewport } from 'next';
import './globals.css';

export const metadata: Metadata = {
  title: 'The Anatomy of a Build — Alpha Concern',
  description:
    'Scroll through a complete architectural rotation. A commercial build by Alpha Concern, Kathmandu — examined from every angle.',
};

export const viewport: Viewport = {
  themeColor: '#003030',
  width: 'device-width',
  initialScale: 1,
  viewportFit: 'cover',
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en">
      <body>{children}</body>
    </html>
  );
}
