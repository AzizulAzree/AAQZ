<section id="bpp-request-basics" class="workspace-section-card bg-white shadow-sm sm:rounded-2xl">
    <button type="button" class="workspace-section-toggle" x-on:click="toggleSection('basics')" :aria-expanded="isOpen('basics')">
        <div class="workspace-section-heading">
            <p class="project-tree-label">{{ __('Request Basics') }}</p>
            <h2 class="workspace-section-title">{{ __('BPP Page 1') }}</h2>
            <p class="workspace-section-copy">{{ __('Main request details.') }}</p>
        </div>
        <span class="workspace-section-chevron" :class="{ 'workspace-section-chevron-open': isOpen('basics') }">&rsaquo;</span>
    </button>

    <div x-cloak x-show="isOpen('basics')">
        <div class="workspace-section-body">
            @if (blank($bpp->b2_kategori_perolehan))
                <form method="POST" action="{{ route('bpp.update', $bpp) }}" class="space-y-8">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="title" value="{{ $bpp->title }}">

                    <section class="rounded-2xl border border-amber-200 bg-amber-50/70 p-5 sm:p-6">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="max-w-2xl">
                                <p class="project-tree-label text-amber-800">{{ __('Main Form Driver') }}</p>
                                <h3 class="mt-2 text-xl font-semibold text-slate-900">{{ __('B2. Kategori Perolehan') }}</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-600">{{ __('Pick the category to set the appendix path.') }}</p>
                            </div>
                            <div class="project-ghost-action">
                                {{ __('Active Appendix') }}:
                                {{ __('Not selected yet') }}
                            </div>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-[minmax(0,1fr)_auto] md:items-end">
                            <div>
                                <x-input-label for="b2_kategori_perolehan" :value="__('B2. Kategori Perolehan')" />
                                <select id="b2_kategori_perolehan" name="b2_kategori_perolehan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500" onchange="this.form.requestSubmit()">
                                    <option value="">{{ __('Select kategori perolehan') }}</option>
                                    <option value="Bekalan" @selected(old('b2_kategori_perolehan', $bpp->b2_kategori_perolehan) === 'Bekalan')>{{ __('Bekalan') }}</option>
                                    <option value="Perkhidmatan" @selected(old('b2_kategori_perolehan', $bpp->b2_kategori_perolehan) === 'Perkhidmatan')>{{ __('Perkhidmatan') }}</option>
                                    <option value="Kerja" @selected(old('b2_kategori_perolehan', $bpp->b2_kategori_perolehan) === 'Kerja')>{{ __('Kerja') }}</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('b2_kategori_perolehan')" />
                            </div>
                            <a href="#bpp-item-details" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50">
                                {{ __('Go To Item Details') }}
                            </a>
                        </div>
                        <p class="mt-3 text-xs text-slate-500">{{ __('This choice is saved once and stays fixed for the draft.') }}</p>
                    </section>
                </form>
            @else
                <section class="rounded-2xl border border-amber-200 bg-amber-50/70 p-5 sm:p-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="max-w-2xl">
                            <p class="project-tree-label text-amber-800">{{ __('Main Form Driver') }}</p>
                            <h3 class="mt-2 text-xl font-semibold text-slate-900">{{ __('B2. Kategori Perolehan') }}</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ __('This choice controls the appendix form and output.') }}</p>
                        </div>
                        <a href="#bpp-item-details" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-white">
                            {{ __('Go To Item Details') }}
                        </a>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-[minmax(0,1fr)_minmax(0,0.8fr)]">
                        <div class="rounded-2xl border border-white/80 bg-white/80 p-5">
                            <p class="project-tree-label">{{ __('Selected Category') }}</p>
                            <p class="mt-2 text-xl font-semibold text-slate-900">{{ $bpp->b2_kategori_perolehan }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/80 bg-white/80 p-5">
                            <p class="project-tree-label">{{ __('Active Appendix') }}</p>
                            <p class="mt-2 text-xl font-semibold text-slate-900">{{ $activeAppendixLabel ? __($activeAppendixLabel) : __('Not selected yet') }}</p>
                        </div>
                    </div>
                </section>
            @endif

            <form id="bpp-request-basics-form" method="POST" action="{{ route('bpp.update', $bpp) }}" class="space-y-8">
                @csrf
                @method('PUT')
                <section class="space-y-4">
                    <div>
                        <p class="project-tree-label">{{ __('Draft Summary') }}</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <x-input-label for="title" :value="__('Draft Title')" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $bpp->title)" required maxlength="255" />
                            <x-input-error class="mt-2" :messages="$errors->get('title')" />
                        </div>
                    </div>
                </section>

                <section class="space-y-4 border-t border-slate-200 pt-8">
                    <div><p class="project-tree-label">{{ __('Header Metadata') }}</p></div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div><x-input-label for="no_rujukan_perolehan" :value="__('No. Rujukan Perolehan')" /><x-text-input id="no_rujukan_perolehan" name="no_rujukan_perolehan" type="text" class="mt-1 block w-full" :value="old('no_rujukan_perolehan', $bpp->no_rujukan_perolehan)" /></div>
                        <div><x-input-label for="tajuk_dokumen" :value="__('Tajuk Dokumen')" /><x-text-input id="tajuk_dokumen" name="tajuk_dokumen" type="text" class="mt-1 block w-full" :value="old('tajuk_dokumen', $bpp->tajuk_dokumen)" /></div>
                        <div><x-input-label for="ruj_dokumen" :value="__('Ruj. Dokumen')" /><x-text-input id="ruj_dokumen" name="ruj_dokumen" type="text" class="mt-1 block w-full" :value="old('ruj_dokumen', $bpp->ruj_dokumen)" /></div>
                        <div><x-input-label for="no_semakan" :value="__('No. Semakan')" /><x-text-input id="no_semakan" name="no_semakan" type="text" class="mt-1 block w-full" :value="old('no_semakan', $bpp->no_semakan)" /></div>
                        <div><x-input-label for="tarikh_kuat_kuasa" :value="__('Tarikh Kuat Kuasa')" /><x-text-input id="tarikh_kuat_kuasa" name="tarikh_kuat_kuasa" type="text" class="mt-1 block w-full" :value="old('tarikh_kuat_kuasa', $bpp->tarikh_kuat_kuasa)" /></div>
                        <div><x-input-label for="muka_surat" :value="__('Muka Surat')" /><x-text-input id="muka_surat" name="muka_surat" type="text" class="mt-1 block w-full" :value="old('muka_surat', $bpp->muka_surat)" /></div>
                    </div>
                </section>

                <section class="space-y-4 border-t border-slate-200 pt-8">
                    <div><p class="project-tree-label">{{ __('A: Perihal Pemohon') }}</p></div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div><x-input-label for="a1_nama_pemohon" :value="__('A1. Nama Pemohon')" /><x-text-input id="a1_nama_pemohon" name="a1_nama_pemohon" type="text" class="mt-1 block w-full" :value="old('a1_nama_pemohon', $bpp->a1_nama_pemohon)" /></div>
                        <div><x-input-label for="a2_jawatan_gred" :value="__('A2. Jawatan / Gred')" /><x-text-input id="a2_jawatan_gred" name="a2_jawatan_gred" type="text" class="mt-1 block w-full" :value="old('a2_jawatan_gred', $bpp->a2_jawatan_gred)" /></div>
                        <div><x-input-label for="a3_jabatan_institusi" :value="__('A3. Jabatan / Institusi')" /><x-text-input id="a3_jabatan_institusi" name="a3_jabatan_institusi" type="text" class="mt-1 block w-full" :value="old('a3_jabatan_institusi', $bpp->a3_jabatan_institusi)" /></div>
                        <div><x-input-label for="a4_no_tel_email" :value="__('A4. No. Tel / E-mel')" /><x-text-input id="a4_no_tel_email" name="a4_no_tel_email" type="text" class="mt-1 block w-full" :value="old('a4_no_tel_email', $bpp->a4_no_tel_email)" /></div>
                    </div>
                </section>

                <section class="space-y-4 border-t border-slate-200 pt-8">
                    <div><p class="project-tree-label">{{ __('Kaedah Perolehan') }}</p></div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2"><x-input-label for="kaedah_perolehan" :value="__('Kaedah Perolehan')" /><x-text-input id="kaedah_perolehan" name="kaedah_perolehan" type="text" class="mt-1 block w-full" :value="old('kaedah_perolehan', $bpp->kaedah_perolehan)" /></div>
                    </div>
                </section>

                <section class="space-y-4 border-t border-slate-200 pt-8">
                    <div><p class="project-tree-label">{{ __('B(I): Perihal Perolehan') }}</p></div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2"><x-input-label for="b1_tajuk_perolehan" :value="__('B1. Tajuk Perolehan')" /><x-text-input id="b1_tajuk_perolehan" name="b1_tajuk_perolehan" type="text" class="mt-1 block w-full" :value="old('b1_tajuk_perolehan', $bpp->b1_tajuk_perolehan)" /></div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                            <p class="project-tree-label">{{ __('Active Category') }}</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">{{ old('b2_kategori_perolehan', $bpp->b2_kategori_perolehan) ?: __('Not selected yet') }}</p>
                        </div>
                        <div><x-input-label for="b3_nilai_tawaran_perolehan" :value="__('B3. Nilai Tawaran Perolehan')" /><x-text-input id="b3_nilai_tawaran_perolehan" name="b3_nilai_tawaran_perolehan" type="text" class="mt-1 block w-full" :value="old('b3_nilai_tawaran_perolehan', $bpp->b3_nilai_tawaran_perolehan)" /></div>
                        <div><x-input-label for="b4_harga_indikatif" :value="__('B4. Harga Indikatif')" /><x-text-input id="b4_harga_indikatif" name="b4_harga_indikatif" type="text" class="mt-1 block w-full" :value="old('b4_harga_indikatif', $bpp->b4_harga_indikatif)" /></div>
                        <div><x-input-label for="b5_peruntukan_diluluskan" :value="__('B5. Peruntukan Diluluskan')" /><x-text-input id="b5_peruntukan_diluluskan" name="b5_peruntukan_diluluskan" type="text" class="mt-1 block w-full" :value="old('b5_peruntukan_diluluskan', $bpp->b5_peruntukan_diluluskan)" /></div>
                        <div class="md:col-span-2"><x-input-label for="b6_justifikasi_keperluan" :value="__('B6. Justifikasi Keperluan')" /><textarea id="b6_justifikasi_keperluan" name="b6_justifikasi_keperluan" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ old('b6_justifikasi_keperluan', $bpp->b6_justifikasi_keperluan) }}</textarea></div>
                        <div><x-input-label for="b7_tajuk_asal_perolehan" :value="__('B7. Tajuk Asal Perolehan')" /><x-text-input id="b7_tajuk_asal_perolehan" name="b7_tajuk_asal_perolehan" type="text" class="mt-1 block w-full" :value="old('b7_tajuk_asal_perolehan', $bpp->b7_tajuk_asal_perolehan)" /></div>
                        <div><x-input-label for="b8_tarikh_diperlukan" :value="__('B8. Tarikh Diperlukan')" /><x-text-input id="b8_tarikh_diperlukan" name="b8_tarikh_diperlukan" type="text" class="mt-1 block w-full" :value="old('b8_tarikh_diperlukan', $bpp->b8_tarikh_diperlukan)" /></div>
                        <div class="md:col-span-2"><x-input-label for="b9_lokasi_diperlukan" :value="__('B9. Lokasi Diperlukan')" /><x-text-input id="b9_lokasi_diperlukan" name="b9_lokasi_diperlukan" type="text" class="mt-1 block w-full" :value="old('b9_lokasi_diperlukan', $bpp->b9_lokasi_diperlukan)" /></div>
                    </div>
                </section>

                <section class="space-y-4 border-t border-slate-200 pt-8">
                    <div><p class="project-tree-label">{{ __('D: Pembekal yang Disyorkan') }}</p></div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div><x-input-label for="d_nama_pembekal" :value="__('D. Nama Pembekal')" /><x-text-input id="d_nama_pembekal" name="d_nama_pembekal" type="text" class="mt-1 block w-full" :value="old('d_nama_pembekal', $bpp->d_nama_pembekal)" /></div>
                        <div><x-input-label for="d_alamat_pembekal" :value="__('D. Alamat Pembekal')" /><x-text-input id="d_alamat_pembekal" name="d_alamat_pembekal" type="text" class="mt-1 block w-full" :value="old('d_alamat_pembekal', $bpp->d_alamat_pembekal)" /></div>
                        <div><x-input-label for="d_no_pendaftaran_syarikat" :value="__('D. No. Pendaftaran Syarikat')" /><x-text-input id="d_no_pendaftaran_syarikat" name="d_no_pendaftaran_syarikat" type="text" class="mt-1 block w-full" :value="old('d_no_pendaftaran_syarikat', $bpp->d_no_pendaftaran_syarikat)" /></div>
                        <div class="md:col-span-2"><x-input-label for="d_kriteria_pemilihan" :value="__('D. Kriteria Pemilihan')" /><textarea id="d_kriteria_pemilihan" name="d_kriteria_pemilihan" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ old('d_kriteria_pemilihan', $bpp->d_kriteria_pemilihan) }}</textarea></div>
                        <div class="md:col-span-2"><x-input-label for="d_lain_lain_kriteria" :value="__('D. Lain-lain Kriteria')" /><textarea id="d_lain_lain_kriteria" name="d_lain_lain_kriteria" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ old('d_lain_lain_kriteria', $bpp->d_lain_lain_kriteria) }}</textarea></div>
                    </div>
                </section>

                <div class="border-t border-slate-200 pt-6">
                    <x-primary-button>{{ __('Save Draft') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</section>
