import { Head } from '@inertiajs/react';
import { AppShell } from '@/components/app-shell';
import { AppContent } from '@/components/app-content';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';

export default function PrivacyPolicyEn() {
  return (
    <AppShell>
      <Head title="Privacy Policy" />
      <AppContent>
        <div className="mx-auto w-full max-w-3xl py-10">
          <Card>
            <CardHeader>
              <CardTitle>Privacy Policy - Inaspro+</CardTitle>
            </CardHeader>
            <CardContent className="space-y-6 text-sm leading-relaxed text-neutral-700 dark:text-neutral-300">
              <p>
                This Privacy Policy explains how we collect, use, store, and protect your personal
                data when using Inaspro+ services, including Marketplace, Communities, Fantasy Events,
                and TV features. By accessing or using our services, you agree to the practices
                described in this Policy.
              </p>

              <Separator />

              <section className="space-y-2">
                <h2 className="text-base font-semibold">Data We Collect</h2>
                <ul className="list-disc space-y-1 pl-5">
                  <li>
                    Account information: name, email address, phone number (if provided), and
                    profile settings.
                  </li>
                  <li>
                    Platform activity: interactions in Communities (posts, comments, images),
                    participation in Fantasy Events (teams, shoe/t-shirt sizes), and display
                    preferences.
                  </li>
                  <li>
                    Transaction data: cart, orders, shipping address, courier rates, and the use of
                    vouchers (global and product-specific).
                  </li>
                  <li>
                    Device and log information: IP address, browser type, and other technical data
                    required for security and service improvements.
                  </li>
                </ul>
              </section>

              <section className="space-y-2">
                <h2 className="text-base font-semibold">How We Use Data</h2>
                <ul className="list-disc space-y-1 pl-5">
                  <li>Provide, operate, and improve Inaspro+ features.</li>
                  <li>Process Marketplace transactions, including shipping, payments, and order status.</li>
                  <li>Enable Community and Fantasy Event activities based on your choices.</li>
                  <li>Prevent fraud, enforce policies, and comply with applicable laws.</li>
                  <li>Offer customer support and service-related communications.</li>
                </ul>
              </section>

              <section className="space-y-2">
                <h2 className="text-base font-semibold">Data Sharing</h2>
                <p>
                  We only share necessary data with third parties that support our services, such as
                  logistics partners, payment providers, or analytics, under appropriate agreements and
                  in compliance with applicable regulations. We do not sell your personal data.
                </p>
              </section>

              <section className="space-y-2">
                <h2 className="text-base font-semibold">Storage & Security</h2>
                <p>
                  Your data is stored securely and only accessed by authorized parties. We implement
                  layered security practices and perform regular backups to maintain data availability
                  and integrity.
                </p>
              </section>

              <section className="space-y-2">
                <h2 className="text-base font-semibold">Your Rights</h2>
                <ul className="list-disc space-y-1 pl-5">
                  <li>Access and update your profile information.</li>
                  <li>Request account deletion in accordance with applicable terms.</li>
                  <li>Manage communication preferences and notifications.</li>
                </ul>
              </section>

              <section className="space-y-2">
                <h2 className="text-base font-semibold">Policy Changes</h2>
                <p>
                  This Privacy Policy may be updated from time to time. Material changes will be
                  communicated through the application. The last updated date will be shown on this
                  page.
                </p>
                <p className="text-xs text-muted-foreground">Last updated: November 2025</p>
              </section>

              <Separator />

              <section className="space-y-2">
                <h2 className="text-base font-semibold">Contact</h2>
                <p>
                  For privacy-related inquiries, please contact our support team through the official
                  channels available in the application.
                </p>
              </section>
            </CardContent>
          </Card>
        </div>
      </AppContent>
    </AppShell>
  );
}