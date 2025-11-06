import { Head } from '@inertiajs/react';
import { AppShell } from '@/components/app-shell';
import { AppContent } from '@/components/app-content';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';

export default function PrivacyPolicy() {
  return (
    <AppShell>
      <Head title="Kebijakan Privasi" />
      <AppContent>
        <div className="mx-auto w-full max-w-3xl py-10">
          <Card>
            <CardHeader>
              <CardTitle>Kebijakan Privasi Inaspro+</CardTitle>
            </CardHeader>
            <CardContent className="space-y-6 text-sm leading-relaxed text-neutral-700 dark:text-neutral-300">
          <p>
            Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, menyimpan,
            dan melindungi data pribadi Anda saat menggunakan layanan Inaspro+, termasuk fitur
            Marketplace, Komunitas, Fantasy Events, dan TV. Dengan mengakses atau menggunakan
            layanan kami, Anda menyetujui praktik yang dijelaskan dalam Kebijakan ini.
          </p>

          <Separator />

          <section className="space-y-2">
            <h2 className="text-base font-semibold">Data yang Kami Kumpulkan</h2>
            <ul className="list-disc space-y-1 pl-5">
              <li>
                Informasi akun: nama, email, nomor telepon, serta
                pengaturan profil.
              </li>
              <li>
                Aktivitas platform: interaksi di Komunitas (post, komentar, gambar), partisipasi
                Fantasy Events (tim, ukuran sepatu/kaos), dan preferensi tampilan.
              </li>
              <li>
                Data transaksi: keranjang, pesanan, alamat pengiriman, tarif kurir, dan penggunaan
                voucher (global maupun produk).
              </li>
              <li>
                Informasi perangkat dan log: alamat IP, jenis browser, dan data teknis lain yang
                diperlukan untuk keamanan dan peningkatan layanan.
              </li>
            </ul>
          </section>

          <section className="space-y-2">
            <h2 className="text-base font-semibold">Cara Kami Menggunakan Data</h2>
            <ul className="list-disc space-y-1 pl-5">
              <li>Menyediakan, mengoperasikan, dan meningkatkan fitur-fitur Inaspro+.</li>
              <li>Memproses transaksi Marketplace, termasuk pengiriman, pembayaran, dan status pesanan.</li>
              <li>Memfasilitasi aktivitas Komunitas dan Fantasy Events sesuai pilihan Anda.</li>
              <li>Melakukan pencegahan penipuan, penegakan kebijakan, dan kepatuhan hukum.</li>
              <li>Memberikan dukungan pelanggan dan komunikasi terkait layanan.</li>
            </ul>
          </section>

          <section className="space-y-2">
            <h2 className="text-base font-semibold">Berbagi Data</h2>
            <p>
              Kami hanya membagikan data yang diperlukan dengan pihak ketiga yang mendukung layanan,
              seperti mitra logistik, penyedia pembayaran, atau analitik, sesuai perjanjian dan
              peraturan yang berlaku. Kami tidak menjual data pribadi Anda.
            </p>
          </section>

          <section className="space-y-2">
            <h2 className="text-base font-semibold">Penyimpanan & Keamanan</h2>
            <p>
              Data Anda disimpan secara aman dan hanya diakses oleh pihak yang berwenang. Kami
              menerapkan praktik keamanan berlapis dan melakukan pencadangan berkala untuk menjaga
              ketersediaan dan integritas data.
            </p>
          </section>

          <section className="space-y-2">
            <h2 className="text-base font-semibold">Hak Anda</h2>
            <ul className="list-disc space-y-1 pl-5">
              <li>Mengakses dan memperbarui informasi profil Anda.</li>
              <li>Meminta penghapusan akun sesuai ketentuan yang berlaku.</li>
              <li>Mengelola preferensi komunikasi dan notifikasi.</li>
            </ul>
          </section>

          {/* Bagian Cookie & Preferensi dihapus karena tidak relevan untuk API/mobile */}

          <section className="space-y-2">
            <h2 className="text-base font-semibold">Perubahan Kebijakan</h2>
            <p>
              Kebijakan Privasi ini dapat diperbarui dari waktu ke waktu. Perubahan material akan
              diinformasikan melalui aplikasi. Tanggal pembaruan terakhir akan ditampilkan pada
              halaman ini.
            </p>
            <p className="text-xs text-muted-foreground">Diperbarui terakhir: November 2025</p>
          </section>

          <Separator />

          <section className="space-y-2">
            <h2 className="text-base font-semibold">Kontak</h2>
            <p>
              Untuk pertanyaan terkait privasi, silakan hubungi tim dukungan melalui kanal resmi
              yang tersedia di aplikasi.
            </p>
          </section>
            </CardContent>
          </Card>
        </div>
      </AppContent>
    </AppShell>
  );
}